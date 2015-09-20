<?php
/**
 * User: Jeremy
 * Date: 9/16/2015
 * Time: 10:25 PM
 */

namespace Jgiberson\JS2Doctrine;


use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Tools\EntityGenerator;


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

        $className = $schema['title'];

        $medatadata = new ClassMetadata($this->getNamespace() . '\\' . $className);

        if(isset($schema['properties']))
        {
            foreach($schema['properties'] as $name => $definition)
            {
                $type = $definition['type'];

                $medatadata->mapField([
                    'fieldName' => $name,
                    'type' => $type
                ]);
            }
        }

        $filename = sprintf("%s/%s/%s.php", $this->getPath(), join('/', explode('\\', $this->getNamespace())) ,$className);
        mkdir(dirname($filename), 0777, true);
        $generator = new EntityGenerator();
        $generator->setGenerateAnnotations(true);
        file_put_contents($filename, $generator->generateEntityClass($medatadata));
    }
}