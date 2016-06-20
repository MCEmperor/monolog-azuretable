<?php
/* 
 * 
 * 
 * 
 */

namespace Huhushow\AzureTableStorageHandler;

use MicrosoftAzure\Storage\Table\TableRestProxy;
use MicrosoftAzure\Storage\Table\Models\Entity;
use MicrosoftAzure\Storage\Common\ServiceException;
use MicrosoftAzure\Storage\Table\Models\EdmType;
use Monolog\Formatter\FormatterInterface;
use Monolog\Formatter\ScalarFormatter;
use Monolog\Formatter\JsonFormatter;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

class AzureTableStorageHandler extends AbstractProcessingHandler
{
	const DATE_FORMAT = 'Y-m-d\TH:i:s.uO';

	/**
	 * @var AzureTableRestProxy Service Builder
	 */
	protected $client;

	/**
	 * @var string
	 */
	protected $table;
	/**
	 * @param AzureTableClient $client
	 * @param string           $table
	 * @param int              $level
	 * @param bool             $bubble
	*/
	public function __construct(TableRestProxy $client, $table, $level = Logger::DEBUG, $bubble = true)
	{
			$this->client = $client;
			$this->table = $table;
			try {
				// Create table.
				$this->client->createTable($this->table);
			} catch(ServiceException $e) {
				$eMsg = $e->getMessage();
				$startPos = strpos($eMsg, '<code>') + 6;
				$endPos = strpos($eMsg, '</code>');
				$eCode = substr($eMsg, $startPos, ($endPos - $startPos));
				if ($eCode == 'TableAlreadyExists'){
				} else {
					echo $eCode;
				}
			}
			parent::__construct($level, $bubble);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function write(array $record)
	{
			$filtered = json_decode($record['formatted'],true);
			$result = $this->flatten($filtered);
			$entity = new Entity();
			$entity->setPartitionKey($result['message'].'-'.$_SERVER['SERVER_NAME']);
			$entity->setRowKey(strval(PHP_INT_MAX - ((int)(microtime(true)*10000))));
			foreach ($result as $k => $v) {
				$entity->addProperty(strval($k), null, strval($v));
			}
			$this->client->insertOrReplaceEntity($this->table, $entity);
	}
	/**
	 * @param  array $record
	 * @return array
	 */
	protected function filterEmptyFields(array $record)
	{
			return array_filter($record, function ($value) {
					return !empty($value) || false === $value || 0 === $value;
			});
	}
	/**
	 * {@inheritdoc}
	 */
	protected function getDefaultFormatter()
	{
			return new JsonFormatter(self::DATE_FORMAT, JsonFormatter::BATCH_MODE_JSON, false);
	}

	protected function flattenInner(array &$out, array $arr, $prefix)
	{
			foreach ($arr as $k => $v) {
					$key = (!strlen($prefix)) ? $k : "{$prefix}__{$k}";
					if (is_array($v)) {
						$this->flattenInner($out, $v, $key);
					} else {
							$out[$key] = $v;
					}
			}
	}

	protected function flatten(array $arr)
	{

			$flat = array();
			$this->flattenInner($flat, $arr, '');
			return $flat;

	}
}