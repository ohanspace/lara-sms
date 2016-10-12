<?php
namespace Ohanspace\Sms\Drivers;



use Ohanspace\Sms\OutgoingMessage;

interface DriverInterface
{
    /**
     * Sends a SMS message.
     *
     * @param \ohanspace\sms\OutgoingMessage $message
     * @return void
     */
    public function send(OutgoingMessage $message);

}
