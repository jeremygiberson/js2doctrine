<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
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

    public function autoload($class)
    {
        $class_parts = explode('\\', $class);

        $shortName = array_pop($class_parts);

        $filename = sprintf("%s.php", $shortName);

        if($this->vfs && $this->vfs->hasChild($filename))
        {
            $path = $this->vfs->getChild($filename)->url();
            include $path;
        }
    }

    /**
     * @Given /^the model generator for namespace "([^"]*)"$/
     */
    public function theModelGeneratorForNamespace($namespace)
    {
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
    }

}
