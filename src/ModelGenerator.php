<?php
/**
 * User: Jeremy
 * Date: 9/16/2015
 * Time: 10:25 PM
 */

namespace Jgiberson\JS2Doctrine;


use Zend\Code\Generator\ClassGenerator;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\PropertyGenerator;

class ModelGenerator
{
    private $path;
    private $namespace;

    /**
     * ModelGenerator constructor.
     * @param $path
     * @param $namespace
     */
    public function __construct($path, $namespace)
    {
        $this->path = $path;
        $this->namespace = $namespace;
    }

    /**
     * @return mixed
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @return mixed
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    public function generate($jsonSchema)
    {
        $schema = json_decode($jsonSchema, true);

        if(!isset($schema['type']) && $schema['type'] !== 'object')
        {
            throw new \RuntimeException("Unable to process the schema");
        }

        if(!isset($schema['title']))
        {
            throw new \RuntimeException("title property must be defined");
        }

        $classGenerator = new ClassGenerator();
        $classGenerator->setName($className = $schema['title']);
        $classGenerator->setNamespaceName($this->getNamespace());

        if(isset($schema['properties']))
        {
            foreach($schema['properties'] as $name => $definition)
            {
                $classGenerator->addProperty($name, null, PropertyGenerator::FLAG_PROTECTED);

                $type = $definition['type'];
                $docBlock = DocBlockGenerator::fromArray([
                    'tags' => [
                        [
                            'name' => sprintf("@Column(type=\"%s\")", $type),
                            'description' => ''
                        ],
                    ],
                ]);

                $classGenerator->getProperty($name)->setDocBlock($docBlock);
            }
        }

        $classDef = $classGenerator->generate();
        $filename = sprintf("%s/%s.php", $this->getPath(), $className);
        file_put_contents($filename, sprintf("<?php\n\n%s", $classDef));
    }
}