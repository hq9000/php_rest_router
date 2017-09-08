<?php

use hq9000\RestApiRouter\Node;
use hq9000\RestApiRouter\Structure;


require_once __DIR__ . '/../src/Node.php';
require_once __DIR__ . '/../src/Structure.php';

class DomainNode extends Node {
    
    private $tag;
    
    public function getTag() {
        return $this->tag;  
    }
    
    public function setTag($tag) {
        $this->tag=$tag;
        return $this;
    }
}


class CommonTest extends PHPUnit_Framework_TestCase {
    
    private $structure;
    
    public function testInitializeStructure() {
        
        $structure=new Structure;
        
        $this->assertFalse($structure->isInitialized());
        
        $structure->setInitializer(function() use ($structure) {
            
            $rootNode=new DomainNode;
            $rootNode->setTag('root');
            
            $classNode1=new DomainNode;
            $classNode1->setTag('class1');
            $classNode1->setPathTrigger(function($remainingPath) {
                if (strpos($remainingPath, 'class1')!==false) {
                    return true;
                }
                return false;
            });
            
            $classNode1->setPathProcessor(function($remainingPath, array &$pathData) use ($structure) {
                $pathData['class']='class1';
                return $structure->cutOffFirstSegment($remainingPath);                
            });
            $classNode1->connectToInputNode($rootNode);            
            
            return $rootNode;            
        });
        
        
        $data=[];
        
        $structure->trace('class1', $data);
        
        
        
        
        
    }
}
