<?php
/**
 * Created by PhpStorm.
 * User: rkerkhof
 * Date: 27-5-16
 * Time: 19:25
 */

namespace WildPHP\Core\Connection\Commands;

class Who extends BaseCommand
{
	/**
	 * @var string
	 */
	protected $channel;

	/**
	 * @var string
	 */
	protected $options = '';

	/**
	 * Who constructor.
	 * @param string $channel
	 * @param string $options
	 */
	public function __construct(string $channel, string $options = '')
	{
		$this->setChannel($channel);
		$this->setOptions($options);
	}

	/**
	 * @return string
	 */
	public function getChannel(): string
	{
		return $this->channel;
	}

	/**
	 * @param string $channel
	 */
	public function setChannel(string $channel)
	{
		$this->channel = $channel;
	}

	/**
	 * @return string
	 */
	public function getOptions(): string
	{
		return $this->options;
	}

	/**
	 * @param string $options
	 */
	public function setOptions(string $options)
	{
		$this->options = $options;
	}

	/**
	 * @return string
	 */
	public function formatMessage(): string
	{
		$options = $this->getOptions();

		return 'WHO ' . $this->getChannel() . (!empty($options) ? ' ' . $options : '') . "\r\n";
	}
}