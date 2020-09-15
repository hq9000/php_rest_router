# PHP REST router
A simple opinionated PHP framework for building REST API structures.

## Motivation

In one of the proprietary projects I was working on, we needed to have a huge and complex REST api surface.
Due to its dynamic nature and deep branching, it was considered suboptimal to go the normal "method per action" way.

What was needed is to be able to define a certain meta-structure and have the endpoints be just as a reflection of it.

## Main concepts

The main concepts of the framework are the ones of:
- a structure (a tree of nodes)
- node traversing

### Structure (a tree of nodes) 

A `Structure` is represented by `hq9000\PhpRestRouter\Structure` class.

![image](https://user-images.githubusercontent.com/21345604/93168690-2899a100-f72c-11ea-8057-523bc74d9577.png)

`Structure` is a unidirectional, acyclic graph of `Nodes`. 

Each node, except the root one, has exactly one input node.

**note:** the arrows on the diagram above rather show the direction of the traversing, and not the direction of association.



Also, every node has associated:
- path processor - something that is responsible for gathering a piece of information relevant to this node
- path trigger - something that will make node traversing choose this node instead of its sibling.
  - if a node is the only output of its upstream node, the trigger can be omitted

### Node traversing

The useful thing a structure makes possible is ability to traverse it.

Traversing is done by calling its `public function trace($path, &$dataAccumulator)`,
where:
- `path` is a string representing an API endpoint
- `dataAccumulator` is a passed-by-reference array that will be used by `path processors` to put information extracted
 from the path at each visited node.
 
The ultimate goal of traversing is to come up with two things:
- the final node
- the fully populated accumulator array

the user of the framework may want to associate certain logic with these bits of information, but this is intentionally 
left out of scope of the framework.
 
### Usage Example

This chapter gives a bit of human-readable explanation to complement reading the source code of `CommonTest.php`.

In that test, we pretend that the useful thing nodes can do is to hold certain `tags`. 
For that, we extend the Node class by `DomainNode`:

```php
/*
 * Here in test we use a simple subclass of a Node modeling some "Domain" class doing 
 * something actually useful. This one can hold some "tag", in reality,
 * it might, for instance, be able to handle a web request etc.
 */
class DomainNode extends Node
{

    /**
     * @var
     */
    private $tag;

    public function getTag()
    {
        return $this->tag;
    }

    public function setTag($tag)
    {
        $this->tag = $tag;
        return $this;
    }
}
```

The structure of these nodes is shown on the simplified diagram below:

![image](https://user-images.githubusercontent.com/21345604/93170671-4f59d680-f730-11ea-8f36-9c221df72fde.png)

The test itself performs the "tracings" and checks their results, for example that `class2/123123` results in the `$idNode` found as the final one and the data array appropriatelly populated with

```json
{
  "class": "class2",
  "id": 123123
}
``` 


 