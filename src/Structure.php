<?php

namespace hq9000\PhpRestRouter;

use hq9000\PhpRestRouter\Node;
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
     * This function returns the final node for this path and, 
     * as a side effect, modifies supplied array 
     * (passed by reference) so that it eventually
     * contains all the data gathered during travel. 
     * 
     * @param type $path
     * @param type $pathData
     * @return Node
     */
    public function trace($path, &$dataAccumulator) {
        if (!$this->isInitialized()) {
            $this->initialize();
        }
        
        /* @var $cursor Node */
        $cursor=$this->rootNode;        
        
        while ($nextNode=$cursor->findOutputNode($path)) {
            $path=$nextNode->processPath($path, $dataAccumulator);
            $cursor=$nextNode;
        }
        
        return $cursor;
    }
    
    
    public function cutOffFirstSegment($path) {
        $tmpArr=explode('/', $path);
        array_shift($tmpArr);
        return implode('/', $tmpArr);
    }
}
