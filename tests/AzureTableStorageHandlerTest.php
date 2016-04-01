<?php

namespace Huhushow\AzureTableStorageHandler\Tests;

use Exception;
use PHPUnit_Framework_TestCase;
use Huhushow\AzureTableStorageHandler\AzureTableStorageHandler;
use Monolog\Logger;
use Huhushow\AzureTableStorageHandler\Tests\TestCase;

class AzureTableStorageHandlerTest extends TestCase
{
  private $client;
  
  public function setUp()
  {
    if (!class_exists('WindowsAzure\Table\TableRestProxy')) {
      $this->markTestSkipped('microsoft/windowsazure SKD not installed');
    }
    
    $this->client = $this->getMockBuilder('WindowsAzure\Table\TableRestProxy')
						->setMethods(array('createTable','__call'))
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
        $record = $this->getRecord();
        $formatter = $this->getMock('Monolog\Formatter\FormatterInterface');
        $formatted = array('foo' => 1, 'bar' => 2);
        $handler = new AzureTableStorageHandler($this->client, 'foo');
        $handler->setFormatter($formatter);
        $formatter
             ->expects($this->once())
             ->method('format')
             ->with($record)
             ->will($this->returnValue($formatted));
        $this->client
             ->expects($this->once())
             ->method('formatAttributes')
             ->with($this->isType('array'))
             ->will($this->returnValue($formatted));
        $this->client
             ->expects($this->once())
             ->method('__call')
             ->with('putItem', array(array(
                 'TableName' => 'foo',
                 'Item' => $formatted,
             )));
        $handler->handle($record);
    }
  
}