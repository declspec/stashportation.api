<?php
class StashController {
    private $_stashService;
    private $_apiService;
    
    public function __construct($StashService, $ApiService) {
        $this->_stashService = $StashService;   
        $this->_apiService = $ApiService;
    }   
    
    public function get($req, $res) {
        $stash = $this->_stashService->findById($req->params["id"]);
        
        return $stash !== null
            ? $this->_apiService->sendSuccess($res, $stash)
            : $this->_apiService->sendNotFound($res, "Unknown stash requested");
    }
    
    public function update($req, $res) {
        $this->_apiService->sendSuccess($res);
    }
    
    public function create($req, $res) {
        $this->_apiService->sendCreated($res);
    }
    
    public function search($req, $res) {
        if (isset($req->query["tags"]))
            return $this->_apiService->sendSuccess($res, $this->_stashService->findByTags(explode(',', $req->query["tags"])));
        else if (isset($req->query["text"]))
            return $this->_apiService->sendSuccess($res, $this->_stashService->search($req->query["text"]));
        else
            return $this->_apiService->sendBadRequest($res);
    }
}