<?php

use hq9000\PhpRestRouter\Node;
use hq9000\PhpRestRouter\Structure;

require_once __DIR__ . '/../src/Node.php';
require_once __DIR__ . '/../src/Structure.php';
require_once __DIR__ . '/../src/Exceptions/PhpRestRouterException.php';
require_once __DIR__ . '/../src/Exceptions/ParseException.php';

use hq9000\PhpRestRouter\Exceptions\ParseException;


/*
 * Here in test we use a simple subclass of a Node modeling some "Domain" class doing 
 * something actually usefull. This one can hold some "tag", in reality, 
 * it might, for instance, be able to handle a web request etc.
 */
class DomainNode extends Node {

    private $tag;

    public function getTag() {
        return $this->tag;
    }

    public function setTag($tag) {
        $this->tag = $tag;
        return $this;
    }

}

class CommonTest extends PHPUnit_Framework_TestCase {
    /* @var $structure Structure */

    protected static $structure;

    
    public static function setupBeforeClass() {
        $structure = new Structure;

        $structure->setInitializer(function() use ($structure)
            {

            $rootNode = new DomainNode;
            $rootNode->setTag('root');

            $classNode1 = new DomainNode;
            $classNode1->setTag('class1');
            $classNode1->setPathTrigger(function($remainingPath)
                {
                if (strpos($remainingPath, 'class1') !== false) {
                    return true;
                }
                return false;
                });

            $classNode1->setPathProcessor(function($remainingPath, array &$pathData) use ($structure)
                {
                $pathData['class'] = 'class1';
                return $structure->cutOffFirstSegment($remainingPath);
                });
            $classNode1->connectToInputNode($rootNode);
            
            
            $classNode2 = new DomainNode;
            $classNode2->setTag('class2');
            $classNode2->setPathTrigger(function($remainingPath)
                {
                if (strpos($remainingPath, 'class2') !== false) {
                    return true;
                }
                return false;
                });

            $classNode2->setPathProcessor(function($remainingPath, array &$pathData) use ($structure)
                {
                $pathData['class'] = 'class2';
                return $structure->cutOffFirstSegment($remainingPath);
                });
            $classNode2->connectToInputNode($rootNode);
            
            $idNode= new DomainNode;
            $idNode->setTag('id');
            $idNode->setPathTrigger(function($remainingPath) use ($structure) {
                
                $firstSegment = $structure->getFirstSegment($remainingPath);
                if (preg_match('/^[0-9]*$/', $firstSegment)) {
                    return true;
                }
                return false;
                
            });

            $idNode->setPathProcessor(function($remainingPath, array &$pathData) use ($structure) {
                $pathData['id'] = intval($structure->getFirstSegment($remainingPath));
                return $structure->cutOffFirstSegment($remainingPath);
            });
            $idNode->connectToInputNode($classNode2);
            
            

            return $rootNode;
            });
            
        self::$structure=$structure;
        
    }
    
    
    public function testSuccessFirstPath() {

        $data = [];

        /* @var $finalNode DomainNode */
        $finalNode = self::$structure->trace('class1', $data);

        $this->assertEquals(DomainNode::class, get_class($finalNode));
        $this->assertEquals('class1', $finalNode->getTag());
        
        // path data must be equal to [ 'class' => 'class1' ], according to our setup
        $this->assertEquals(1, count(array_keys($data)));
        $this->assertEquals('class1', $data['class']);        
    }
    
    public function testSuccessSecondPath() {

        $data = [];

        /* @var $finalNode DomainNode */
        $finalNode = self::$structure->trace('class2/123123', $data);

        
        $this->assertEquals(2, count(array_keys($data)));
        
    }
    
    
    public function testFailure() {
                
        // there is no class 3 node connected to the root
        $this->verifyThatFailsWithException(self::$structure, 'class3', ParseException::class);
        
        // "ids" after class 2 must be numeric, abc is not numeric
        $this->verifyThatFailsWithException(self::$structure, 'class2/abc', ParseException::class);
        
    }
    
    private function verifyThatFailsWithException(Structure $structure, $path, $exceptionClass) {
        $caught=false;
        $unexpectedCaught=false;
        try {
            $data=[];
            $structure->trace($path, $data);
        } catch (Exception $e) {
            if (get_class($e)==$exceptionClass) {
                $caught=true;
            } else {
                $unexpectedCaught=true;
            }
        }        
        $this->assertFalse($unexpectedCaught, 'unexpected exception was thrown ' . $e);
        $this->assertTrue($caught,'expected exception was not caught');        
        
    }

}
