<?php
/**
 * Created by PhpStorm.
 * User: yanwsh
 * Date: 11/29/14
 * Time: 10:02 PM
 */

namespace ebid\Event;

use Symfony\Component\EventDispatcher\Event;
use ebid\Event\RegisterEvent;

class EmailListener {

    public function sendEmailOnRegistration(RegisterEvent $event){
        global $container;
        $user = $event->getUser();
        $message = \Swift_Message::newInstance($user->getUsername() . ', Welcome to ebid')
            ->setFrom($container->getParameter('mail_email'), 'ebid')
            ->setTo(array($user->getEmail() => $user->getUsername()))
            ->setBody('Hello ' . $user->getUsername() .', Thank you for your registration.');

        $mailer = $container->get('swiftMailer');
        try{
            $result = $mailer->send($message);
        }catch (\Exception $e){
        }


    }

    public function sendEmailOnBidFinish(BidResultEvent $event){
        global $container;
        $winlists = $event->getWinLists();
        $loselists = $event->getLoseLists();

        if($winlists != null){
            foreach($winlists as $winner){
                $message = \Swift_Message::newInstance($winner['username'] . ', Congratulations, it\'s all yours!')
                    ->setFrom($container->getParameter('mail_email'), 'ebid')
                    ->setTo(array($winner['email'] => $winner['username']))
                    ->setBody('<html><head>
                                </head>
                                <body>
	                                <div style="margin-top: 8%;margin-left:10%;">
		                                <img src="'. $container->getParameter('server_url') .'images/congra.jpg" alt="congratulation" width="200" height="140">
		                                    <p>Congratulation! '. $winner['username'].'</p>
		                            <p>You just win the product '. $winner['pname'] .'.</p>
		                            <p> ebid will update you when your order ships.</p>
	                                </div>
                                </body>
                                </html>', 'text/html');

                $mailer = $container->get('swiftMailer');
                try{
                    $result = $mailer->send($message);
                }catch (\Exception $e){
                }
            }
        }

        if($loselists != null){
            foreach($loselists as $loser){
                $message = \Swift_Message::newInstance($loser['username'] . ', Thank you for your participation')
                    ->setFrom($container->getParameter('mail_email'), 'ebid')
                    ->setTo(array($loser['email'] => $loser['username']))
                    ->setBody('<html><head>
                        </head>
                        <body>
	                        <div style="margin-top: 6.5%;margin-left:10%;">
		                        <img src="'. $container->getParameter('server_url') .'images/crybaby.jpg" alt="Keep trying" width="200" height="140" />
		                        <p>Sorry, ' . $loser['username'] .'. Thank you for your participation. </p>
		                        <p>You didn\'t win the product '. $loser['pname'] .'</p>
		                        <p>Stay cool and keep trying!</p>
	                        </div>
                        </body>
                    </html>', 'text/html');
                $mailer = $container->get('swiftMailer');
                try{
                    $result = $mailer->send($message);
                }catch (\Exception $e){
                }
            }
        }

    }
} 