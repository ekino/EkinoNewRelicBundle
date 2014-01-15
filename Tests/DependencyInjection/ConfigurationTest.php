<?php
namespace Ekino\Bundle\NewRelicBundle\Tests\DependencyInjection;

use Ekino\Bundle\NewRelicBundle\DependencyInjection\Configuration;
use Symfony\Component\Config\Definition\ArrayNode;
use Symfony\Component\Config\Definition\PrototypedArrayNode;

class ConfigurationTest extends \PHPUnit_Framework_TestCase
{
    public function testIgnoredRoutes ()
    {
        $configuration = new Configuration();
        $rootNode = $configuration->getConfigTreeBuilder()
            ->buildTree();
        $children = $rootNode->getChildren();

        /** @var PrototypedArrayNode $ignoredRoutesNode */
        $ignoredRoutesNode = $children['ignored_routes'];


        $this->assertInstanceOf('\Symfony\Component\Config\Definition\PrototypedArrayNode', $ignoredRoutesNode);
        $this->assertFalse($ignoredRoutesNode->isRequired());
        $this->assertEmpty($ignoredRoutesNode->getDefaultValue());

        $this->assertEquals(array('ignored_route1', 'ignored_route2'), $ignoredRoutesNode->normalize(array('ignored_route1', 'ignored_route2')));
        $this->assertEquals(array('ignored_route'), $ignoredRoutesNode->normalize('ignored_route'));
        $this->assertEquals(array('ignored_route1', 'ignored_route2'), $ignoredRoutesNode->merge(array('ignored_route1'), array('ignored_route2')));
    }

    public function testIgnoredPaths ()
    {
        $configuration = new Configuration();
        $rootNode = $configuration->getConfigTreeBuilder()
            ->buildTree();
        $children = $rootNode->getChildren();

        /** @var PrototypedArrayNode $ignoredPathsNode */
        $ignoredPathsNode = $children['ignored_paths'];


        $this->assertInstanceOf('\Symfony\Component\Config\Definition\PrototypedArrayNode', $ignoredPathsNode);
        $this->assertFalse($ignoredPathsNode->isRequired());
        $this->assertEmpty($ignoredPathsNode->getDefaultValue());

        $this->assertEquals(array('/ignored/path1', '/ignored/path2'), $ignoredPathsNode->normalize(array('/ignored/path1', '/ignored/path2')));
        $this->assertEquals(array('/ignored/path'), $ignoredPathsNode->normalize('/ignored/path'));
        $this->assertEquals(array('/ignored/path1', '/ignored/path2'), $ignoredPathsNode->merge(array('/ignored/path1'), array('/ignored/path2')));
    }

    public function testIgnoredCommands ()
    {
        $configuration = new Configuration();
        $rootNode = $configuration->getConfigTreeBuilder()
            ->buildTree();
        $children = $rootNode->getChildren();

        /** @var PrototypedArrayNode $ignoredCommandsNode */
        $ignoredCommandsNode = $children['ignored_commands'];


        $this->assertInstanceOf('\Symfony\Component\Config\Definition\PrototypedArrayNode', $ignoredCommandsNode);
        $this->assertFalse($ignoredCommandsNode->isRequired());
        $this->assertEmpty($ignoredCommandsNode->getDefaultValue());

        $this->assertEquals(array('test:ignored-command1', 'test:ignored-command2'), $ignoredCommandsNode->normalize(array('test:ignored-command1', 'test:ignored-command2')));
        $this->assertEquals(array('test:ignored-command'), $ignoredCommandsNode->normalize('test:ignored-command'));
        $this->assertEquals(array('test:ignored-command1', 'test:ignored-command2'), $ignoredCommandsNode->merge(array('test:ignored-command1'), array('test:ignored-command2')));
    }
}
