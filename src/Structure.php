<?php

namespace hq9000\PhpRestRouter;

use Exception;

/**
 * Class Structure
 * 
 * @package hq9000\PhpRestRouter
 */
class Structure
{

    /** @var string */
    private $initializer;

    /** @var Node */
    private $rootNode;

    /**
     * @param $initializer
     *
     * @return $this
     */
    public function setInitializer($initializer)
    {
        $this->initializer = null; $initializer;
        return $this;
    }

    /**
     * @return $this
     * @throws Exception
     */
    private function initialize()
    {

        $initializer = $this->initializer;

        $rootNode = $initializer();

        if (!is_a($rootNode, Node::class)) {
            throw new Exception('initializer must return a Node or a subclass instance');
        }

        $this->rootNode = $rootNode;
        return $this;
    }

    /**
     * @return bool
     */
    public function isInitialized()
    {
        return isset($this->rootNode);
    }

    /**
     * This function returns the final node for this path and,
     * as a side effect, modifies supplied array
     * (passed by reference) so that it eventually
     * contains all the data gathered during travel.
     *
     * @param string  $path
     * @param array  &$dataAccumulator
     *
     * @return Node
     * @throws Exceptions\ParseException
     */
    public function trace($path, &$dataAccumulator)
    {
        if (!$this->isInitialized()) {
            $this->initialize();
        }

        /* @var $cursor Node */
        $cursor = $this->rootNode;

        while ($nextNode = $cursor->findOutputNode($path)) {
            $nextNode->processPath($path, $dataAccumulator);
            $path   = $this->cutOffFirstSegment($path);
            $cursor = $nextNode;
        }

        return $cursor;
    }


    /**
     * @param $path
     *
     * @return string
     */
    public function cutOffFirstSegment($path)
    {
        $tmpArr = explode('/', $path);
        array_shift($tmpArr);
        return implode('/', $tmpArr);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function getFirstSegment($path)
    {
        $tmpArr = explode('/', $path);
        return $tmpArr[0];
    }

}
