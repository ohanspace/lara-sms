<?php
namespace Ohanspace\Sms;

use Closure;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Illuminate\Support\Serial;
use Illuminate\Container\Container;
use Ohanspace\Sms\Drivers\DriverInterface;

class SMS
{
    /**
     * The Driver Interface instance.
     *
     * @var \ohanspace\sms\Drivers\DriverInterface
     */
    protected $driver;

    /**
     * The log writer instance.
     *
     * @var \Illuminate\Log\Writer
     */
    protected $logger;

    /**
     * Determines if a message should be sent or faked.
     *
     * @var boolean
     */
    protected $pretending = false;

    /**
     * The IOC Container
     *
     * @var \Illuminate\Container\Container
     */
    protected $container;

    /**
     * The global from address
     *
     * @var string
     */
    protected $from;
    /*
    *  delivery status
    */
    protected $delivered = false;


    /**
     * Creates the SMS instance.
     *
     * @param DriverInterface $driver
     */
    public function __construct(DriverInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * Changes the set SMS driver
     *
     * @param $driver
     */
    public function driver($driver)
    {
        $this->container['sms.sender'] = $this->container->share(function ($app) use ($driver) {
            return (new DriverManager($app))->driver($driver);
        });

        $this->driver = $this->container['sms.sender'];
    }

    /**
     * Send a SMS.
     *
     * @param string $view The desired view.
     * @param array $data The data that needs to be passed into the view.
     * @param \Closure $callback The methods that you wish to fun on the message.
     */
    public function send($text, $callback)
    {
        $data['message'] = $message = $this->createOutgoingMessage();

        //We need to set the properties so that we can later pass this onto the Illuminate Mailer class if the e-mail gateway is used.
        $message->setText($text);

        call_user_func($callback, $message);

        if (!$this->pretending)
        {
            $sms =  $this->driver->send($message);
            $this->logMessage($message);
            return $sms;

        }
        elseif (isset($this->logger))
        {
            $this->setDeliveryReport(true);
            $this->logMessage($message);


            //dump($this);
            return $this;
        }
    }

    /**
     * Logs that a message was sent.
     *
     * @param $message
     */
    protected function logMessage($message)
    {
        $numbers = implode(" , ", $message->getTo());
        $message = $message->getText();
        //dump($message);
        $mode = "fake";
        $delivered = $this->delivered();
        if(!$this->isPretending()){
            $mode = 'actual';
            $delivered = $this->driver->delivered();
            //dump($delivered);
        }
        $delivered = ($delivered)?'SUCCESS':'FAILED';

        $this->logger->info("#MODE: $mode   #DELIVERED: $delivered  #MESSAGE: $message  #NUMBER: $numbers ");
    }

    /**
     * Creates a new Message instance.
     *
     * @return \ohanspace\sms\OutgoingMessage
     */
    protected function createOutgoingMessage()
    {
        $message = new OutgoingMessage();

        //If a from address is set, pass it along to the message class.
        if (isset($this->from)) {
            $message->from($this->from);
        }

        return $message;
    }

    /**
     * Returns if the message should be faked when sent or not.
     *
     * @return boolean
     */
    public function isPretending()
    {
        return $this->pretending;
    }

    /**
     * Fake sending a SMS
     *
     * @param $view The desired view
     * @param $data The data to fill the view
     * @param $callback The message callback
     */
    public function pretend($text, $callback)
    {
        $this->setPretending(true);
        return $this->send($text, $callback);
    }

    /**
     * Sets if SMS should be fake send a SMS
     *
     * @param bool $pretend
     */
    public function setPretending($pretend = false)
    {
        $this->pretending = $pretend;
    }

    /**
     * Sets the IoC container
     *
     * @param Container $container
     */
    public function setContainer(Container $container)
    {
        $this->container = $container;
    }

    /**
     * Sets the number that message should always be sent from.
     *
     * @param $number
     */
    public function alwaysFrom($number)
    {
        $this->from = $number;
    }

    /**
     * Set the log writer instance.
     *
     * @param  \Illuminate\Log\Writer $logger
     * @return $this
     */
    public function setLogger()
    {
        $log = new Logger('sms');
        $log->pushHandler(new StreamHandler(storage_path('logs/sms.log'), Logger::INFO));

        $this->logger = $log;

        return $this;
    }

    /*
     * set delivered
     */

    public function setDeliveryReport($status = false){

        $this->delivered = $status;
    }

    /*
     * get delivered
     */
    public function delivered(){

        return $this->delivered;
    }


}
