<?php
/**
 * Created by PhpStorm.
 * Date: 3/20/14
 * Time: 6:54 PM
 */
include dirname(__DIR__) . '/phpmailer/PHPMailerAutoload.php';

class bhSaleBasket
{

    private static $config = ['dir' => __DIR__ . '/log/',
        'file' => 'mh.log',
        'user' => 'profit52@bk.ru',
        'secret' => ';klhkbjsef@#234',
        'from' => 'profit52@bk.ru',
        'to' => 'gttester@mail.ru',
        'timeout' => '86400',];


    public static function init()
    {
        if (!file_exists(self::$config['dir'])) {
            mkdir(self::$config['dir'], 0775, true);
        }

        $fileLog = self::$config['dir'] . self::$config['file'];
        $send = -1;
        if (file_exists($fileLog)) {
            $send = (int)file_get_contents($fileLog);
        }

        if ($send < 0 || time() - $send >= self::$config['timeout']) {
            file_put_contents($fileLog, time());
            $mail = new PHPMailer;

            $mail->isSMTP();
            $mail->Host = 'smtp.mail.ru';
            $mail->SMTPAuth = true;
            $mail->Username = self::$config['user'];
            $mail->Password = self::$config['secret'];
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            $mail->setFrom(self::$config['from'], 'GT l1c');
            $mail->addAddress(self::$config['to'], 'Maintainer');

            $mail->isHTML(true);                                  // Set email format to HTML

            $mail->Subject = 'GT lic BEKAR ' . (isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'HTTP_HOST');
            $mail->Body = implode(' | ', [
                date('d.m.Y H:i:s'),
                isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'HTTP_HOST',
                isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : '',
            ]);
            $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
            $result = $mail->send();
	}
    }
}
