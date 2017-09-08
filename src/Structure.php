<?php

namespace hq9000\RestApiRouter;

use hq9000\RestApiRouter\Node;
use Exception;

class Structure {
    
    private $initializer;
    
    /* @var $rootNode Node */
    private $rootNode;
    
    public function setInitializer($initializer) {
        $this->initializer=$initializer;
        return $this;
    }    
    
    private function initialize() {
        
        $initializer=$this->initializer;
        
        $rootNode=$initializer();
        
        if (!is_a($rootNode, Node::class)) {
            throw new Exception('initializer must return a Node or a subclass instance');
        }
        
        $this->rootNode=$rootNode;
        return $this;        
    }
    
    public function isInitialized() {
        return isset($this->rootNode);
    }
    
    /**
     * 
     * @param type $path
     * @param type $pathData
     * @return array
     */
    public function trace($path) {
        if (!$this->isInitialized()) {
            $this->initialize();
        }
        
        /* @var $cursor Node */
        $cursor=$this->rootNode;        
        $pathDataAccumulator=[];
        
        while ($nextNode=$cursor->findOutputNode($path)) {
            $path=$nextNode->processPath($path, $pathDataAccumulator);
            $cursor=$nextNode;
        }
        
        return [ $cursor, $pathDataAccumulator ];
    }
    
    
    public function cutOffFirstSegment($path) {
        $tmpArr=explode('/', $path);
        array_shift($tmpArr);
        return implode('/', $tmpArr);
    }
}
