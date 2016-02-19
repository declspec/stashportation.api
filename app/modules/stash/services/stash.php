<?php
class StashService {
    private $_db;
    
    public function __construct($Database) {
        $this->_db = $Database;    
    }
    
    public function findById($id, $includeMarkdown=true, $includeTags=true) {
        return $this->findOneStashWhere('s.id = :id', array(':id' => intval($id)), $includeMarkdown, $includeTags);
    }   
    
    public function search($text, $includeMarkdown=false) {
        $regexp = implode('|', array_map('preg_quote', preg_split('/[,\s]+/', strtolower($text))));
        
        // Currently only searches by title. Could also use the REGEXP on markdown.
        // but it'd likely be slow as shit once we get more than a couple of decent-sized records.
        return $this->findStashWhere(
            "LOWER(s.title) REGEXP :search", 
            array(":search" => $regexp),
            false,
            true
        );
    }
    
    public function findByTags(array $tags, $includeMarkdown=false) {
        $foundTags = $this->findTagsWhere(
            't.tag IN (' . implode(',', array_fill(0, count($tags), '?')) . ')',
            array_map('strtolower', $tags)
        );
        
        if (count($foundTags) === 0)
            return array();
        
        return $this->findStashWhere(
            'st.tag_id IN (' . implode(',', array_fill(0, count($foundTags), '?')) . ')',
            array_map(function($t) { return $t->id; }, $foundTags),
            $includeMarkdown,
            true
        );
    }
    
    private function findStashWhere($clause, $params, $includeMarkdown, $includeTags, $emulatePrepare=true) {
        $select = 'SELECT s.id, s.title, s.date_created AS dateCreated, s.date_modified AS dateModified';
        $source = 'FROM stash s';
        $extra = '';
        
        if ($includeMarkdown) {
            $select .= ', s.markdown';
        }
        
        if ($includeTags) {
            $select .= ', GROUP_CONCAT(t.tag) AS tags';
            $source .= ' INNER JOIN stash_tags st ON st.stash_id = s.id INNER JOIN tag t ON t.id = st.tag_id';
            $extra .= ' GROUP BY s.id';
        }
        
        $sql = implode("\n", array($select, $source, $clause ? "WHERE {$clause}" : '', $extra));
        return array_map('self::mapStash', $this->_db->query($sql, $params, $emulatePrepare));
    }
    
    private function findTagsWhere($clause, $params, $emulatePrepare=true) {
        $sql = 'SELECT t.id, t.tag, t.date_created AS dateCreated FROM tag t' . ($clause ? " WHERE {$clause}" : '');            
        return $this->_db->query($sql, $params, $emulatePrepare);
    }
    
    private function findOneStashWhere($clause, $params, $includeMarkdown, $includeTags, $emulatePrepare=true) {
        $data = $this->findStashWhere($clause, $params, $includeMarkdown, $includeTags, $emulatePrepare);
        return count($data) === 1 ? $data[0] : null;   
    }
    
    private static function mapStash($record) {
        if (isset($record->tags))
            $record->tags = array_map('trim', explode(',', $record->tags));
            
        return $record;
    }
};