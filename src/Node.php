<?php

namespace hq9000\PhpRestRouter;

use Exception;
use hq9000\PhpRestRouter\Exceptions\ParseException;

class Node {

    private $pathProcessor=null;
    private $pathTrigger=null;
    
    public function getHandler() {
        if ($this->hasHandler())
        Assertions::assertTrue($this->hasHandler(), 'this node has no handler');
        return $this->handler;
    }
   
    public function triggersToPath($path) {
        $pathTriggerFunction=$this->pathTrigger;
        return $pathTriggerFunction($path);
    }
    
    public function processPath($remainingPath, array &$pathData) {
        if (!is_string($remainingPath)) {
            throw new Exception('remaining path should be string');
        }
        $processorFunction=$this->pathProcessor;
        return $processorFunction($remainingPath, $pathData);
    }
    
    public function setPathProcessor($processor) {
        $this->pathProcessor=$processor;
        return $this;
    }
    
    public function setPathTrigger($trigger) {
        $this->pathTrigger=$trigger;
        return $this;
    }
    
    public function connectToOutputNode(Node $outputNode) {
        $this->outputNodes[]=$outputNode;
        return $this;
    }
    
    public function connectToInputNode(Node $inputNode) {
        $inputNode->connectToOutputNode($this);
    }
    
    public function findOutputNode($remainingPath) {
        
        if ($remainingPath=='') {
            return false;
        }
        
        foreach ($this->outputNodes as $outputNode) {
            if ($outputNode->triggersToPath($remainingPath)) {
                return $outputNode;
            }            
        }
        
        throw new ParseException('path is not fully parsed yet, but output node can\'t be dertermined. Path remainder is ' . $remainingPath);
    }
    
    public function handle($pathData, InternalApiRequest $request) {
        $handler=$this->getHandler();
        return $handler($pathData, $request);
    }

}
