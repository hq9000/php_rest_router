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

            return $rootNode;
            });
            
        self::$structure=$structure;
        
    }
    
    
    public function testSuccess() {

        $data = [];

        /* @var $finalNode DomainNode */
        $finalNode = self::$structure->trace('class1', $data);

        $this->assertEquals(DomainNode::class, get_class($finalNode));
        $this->assertEquals('class1', $finalNode->getTag());
        
        // path data must be equal to [ 'class' => 'class1' ], according to our setup
        $this->assertEquals(1, count(array_keys($data)));
        $this->assertEquals('class1', $data['class']);        
    }
    
    
    public function testFailure() {
        
        // to support older phpunit versions which do not have expectException                
        $caught=false;
        
        try {
            // this shouldnt work as the node next to the root wont be selected, this must fail       
            $finalNode = self::$structure->trace('class2', $data);
        } catch(ParseException $e) {
            $caught=true;
        }
        
        $this->assertTrue($caught);
        
    }

}
