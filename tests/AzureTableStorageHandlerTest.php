<?php

namespace Huhushow\AzureTableStorageHandler\Test;

use Exception;
use PHPUnit_Framework_TestCase;
use Huhushow\AzureTableStorageHandler\AzureTableStorageHandler;
use Monolog\Logger;
use Huhushow\AzureTableStorageHandler\Test\TestCase;

class AzureTableStorageHandlerTest extends TestCase
{
  private $client;
  private $entity;
  
  public function setUp()
  {
    if (!class_exists('WindowsAzure\Table\TableRestProxy')) {
      $this->markTestSkipped('microsoft/windowsazure SKD not installed');
    }
    
    $this->client = $this->getMockBuilder('WindowsAzure\Table\TableRestProxy')
      ->setMethods(array('createTable','insertOrReplaceEntity'))
      ->disableOriginalConstructor()->getMock();
  }
	
	public function testConstruct()
	{
		$this->assertInstanceOf('Huhushow\AzureTableStorageHandler\AzureTableStorageHandler', new AzureTableStorageHandler($this->client, 'foo'));
	}
	
	public function testInterface()
	{
		$this->assertInstanceOf('Monolog\Handler\HandlerInterface', new AzureTableStorageHandler($this->client, 'foo'));
	}
	
	public function testGetFormatter()
	{
		$handler = new AzureTableStorageHandler($this->client, 'foo');
		$this->assertInstanceOf('Monolog\Formatter\JsonFormatter', $handler->getFormatter());
	}
	
	public function testHandle()
    {
        $msg = array(
            'level' => Logger::ERROR,
            'level_name' => 'ERROR',
            'channel' => 'meh',
            'context' => array('foo' => 7, 'bar', 'class' => new \stdClass),
            'datetime' => new \DateTime("@0"),
            'extra' => array(),
            'message' => 'log',
        );
        $handler = new AzureTableStorageHandler($this->client, 'foo');
        
        $handler->handle($msg);
    }
  
}