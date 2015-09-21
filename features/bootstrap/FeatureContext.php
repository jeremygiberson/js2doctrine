<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
use Doctrine\Common\Annotations\SimpleAnnotationReader;
use Doctrine\ORM\Mapping\Column;
use Jgiberson\JS2Doctrine\ModelGenerator;
use org\bovigo\vfs\vfsStream;
use org\bovigo\vfs\vfsStreamDirectory;
use PHPUnit_Framework_Assert as PHPUnit;

/**
 * Defines application features from the specific context.
 */
class FeatureContext implements Context, SnippetAcceptingContext
{
    /** @var  vfsStreamDirectory */
    private $vfs;
    /** @var  ModelGenerator */
    private $generator;

    /**
     * FeatureContext constructor.
     */
    public function __construct()
    {
        AnnotationRegistry::registerFile(__DIR__ . '/../../vendor/doctrine/orm/lib/Doctrine/ORM/Mapping/Driver/DoctrineAnnotations.php');
    }


    public function autoload($class)
    {
        $class_parts = explode('\\', $class);

        array_unshift($class_parts, $this->vfs->url());

        $filename = sprintf("%s.php", join('/', $class_parts));

        if(file_exists($filename))
        {
            include $filename;
        }
    }

    /**
     * @Given /^the model generator for namespace "([^"]*)"$/
     */
    public function theModelGeneratorForNamespace($namespace)
    {
        if(isset($this->vfs)){
            unset($this->vfs);
        }

        $this->vfs = vfsStream::setup('root');
        spl_autoload_register([$this, 'autoload'], false);

        $this->generator = new ModelGenerator($this->vfs->url(), $namespace);
    }

    /**
     * @Given /^I generate models for "([^"]*)"$/
     */
    public function iGenerateModelsFor($schema)
    {
        $path = sprintf("%s/../fixtures/%s", __DIR__, $schema);

        if(!file_exists($path))
        {
            throw new \RuntimeException("Could not load schema from ".$path);
        }

        $this->generator->generate(file_get_contents($path));
    }

    /**
     * @Then /^the class "([^"]*)" should exist$/
     */
    public function theClassShouldExist($shortName)
    {
        $namespace = $this->generator->getNamespace();

        PHPUnit::assertTrue(class_exists($namespace . '\\' . $shortName));
    }

    /**
     * @Given /^the "([^"]*)" attribute "([^"]*)" should exist$/
     */
    public function theAttributeShouldExist($shortName, $attribute)
    {
        $class = $this->generator->getNamespace() . "\\" . $shortName;
        $reflection = new ReflectionClass($class);
        PHPUnit::assertTrue($reflection->hasProperty($attribute));
    }

    /**
     * @Given /^the "([^"]*)" attribute "([^"]*)" annotations should contain '([^']*)'$/
     */
    public function theAttributeAnnotationsShouldContain($shortName, $attribute, $needle)
    {
        $class = $this->generator->getNamespace() . "\\" . $shortName;
        $reflection = new ReflectionClass($class);
        PHPUnit::assertTrue($reflection->hasProperty($attribute));
        $property = $reflection->getProperty($attribute);
        $annotations = $property->getDocComment();
        PHPUnit::assertContains($needle, $annotations);

        $reader = new AnnotationReader();
        $annotations = $reader->getPropertyAnnotations($property);
    }

    /**
     * @Given /^the "([^"]*)" attribute "([^"]*)" is of type "([^"]*)"$/
     */
    public function theAttributeIsOfType($shortName, $attribute, $type)
    {
        $class = $this->generator->getNamespace() . "\\" . $shortName;
        $reflection = new ReflectionClass($class);
        PHPUnit::assertTrue($reflection->hasProperty($attribute));
        $property = $reflection->getProperty($attribute);

        $reader = new AnnotationReader();
        /** @var Column $columnAnnotation */
        $columnAnnotation = $reader->getPropertyAnnotation($property, Column::class);
        PHPUnit::assertEquals($type, $columnAnnotation->type);
    }

    /**
     * @Given /^the "([^"]*)" attribute "([^"]*)" is "([^"]*)"$/
     */
    public function theAttributeIs($shortName, $attribute, $annotationProperty)
    {
        $class = $this->generator->getNamespace() . "\\" . $shortName;
        $reflection = new ReflectionClass($class);
        PHPUnit::assertTrue($reflection->hasProperty($attribute));
        $property = $reflection->getProperty($attribute);

        $reader = new AnnotationReader();
        /** @var Column $columnAnnotation */
        $columnAnnotation = $reader->getPropertyAnnotation($property, Column::class);
        PHPUnit::assertTrue($columnAnnotation->{$annotationProperty});

    }

    /**
     * @Given /^the "([^"]*)" attribute "([^"]*)" is not "([^"]*)"$/
     */
    public function theAttributeIsNot($shortName, $attribute, $annotationProperty)
    {
        $class = $this->generator->getNamespace() . "\\" . $shortName;
        $reflection = new ReflectionClass($class);
        PHPUnit::assertTrue($reflection->hasProperty($attribute));
        $property = $reflection->getProperty($attribute);

        $reader = new AnnotationReader();
        /** @var Column $columnAnnotation */
        $columnAnnotation = $reader->getPropertyAnnotation($property, Column::class);
        PHPUnit::assertNotTrue($columnAnnotation->{$annotationProperty});
    }

}
