<?php

namespace hq9000\PhpRestRouter;

use Exception;
use hq9000\PhpRestRouter\Exceptions\ParseException;

/**
 * Class Node
 *
 * @package hq9000\PhpRestRouter
 */
class Node {

    /** @var string */
    private $pathProcessor=null;

    /** @var string  */
    private $pathTrigger=null;

    /** @var Node[] */
    private $outputNodes;

    /**
     * @param $path
     *
     * @return mixed
     */
    public function triggersToPath($path) {
        $pathTriggerFunction=$this->pathTrigger;
        return $pathTriggerFunction($path);
    }

    /**
     * @return bool
     */
    public function hasProcessor() {
        return isset($this->pathProcessor);
    }


    /**
     * @param       $remainingPath
     * @param array $pathData
     *
     * @throws Exception
     */
    public function processPath($remainingPath, array &$pathData) {
        if (!is_string($remainingPath)) {
            throw new Exception('remaining path should be string');
        }
        if ($this->hasProcessor()) {
            $processorFunction = $this->pathProcessor;
            $processorFunction($remainingPath, $pathData);
        }
    }

    /**
     * @param $processor
     *
     * @return $this
     */
    public function setPathProcessor($processor) {
        $this->pathProcessor=$processor;
        return $this;
    }

    /**
     * @param $trigger
     *
     * @return $this
     */
    public function setPathTrigger($trigger) {
        $this->pathTrigger=$trigger;
        return $this;
    }

    /**
     * @param Node $outputNode
     *
     * @return $this
     */
    public function connectToOutputNode(Node $outputNode) {
        $this->outputNodes[]=$outputNode;
        return $this;
    }

    /**
     * @param Node $inputNode
     */
    public function connectToInputNode(Node $inputNode) {
        $inputNode->connectToOutputNode($this);
    }

    /**
     * @param $remainingPath
     *
     * @return Node|bool
     * @throws ParseException
     */
    public function findOutputNode($remainingPath) {
        
        if ($remainingPath=='') {
            return false;
        }
        
        foreach ($this->outputNodes as $outputNode) {
            if ($outputNode->triggersToPath($remainingPath)) {
                return $outputNode;
            }            
        }
        
        throw new ParseException('path is not fully parsed yet, but output node can\'t be determined. Path remainder is ' . $remainingPath);
    }
}
