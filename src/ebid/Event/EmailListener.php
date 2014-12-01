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
        $result = $mailer->send($message);
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
                    ->setBody('<html><head><style>
	                            #wrapper {
	                                margin-top: 8%;
		                            margin-left:10%;
	                            }
	                            </style>
                                </head>
                                <body>
	                                <div id="wrapper">
		                                <img src="images/congra.jpg" alt="congratulation" width="200" height="140">
		                                    <p>Congratulation! '. $winner['username'].'</p>
		                            <p>You just win the bid.</p>
		                            <p> ebid will update you when your order ships.</p>
	                                </div></body>');

                $mailer = $container->get('swiftMailer');
                $result = $mailer->send($message);
            }
        }

        if($loselists != null){
            foreach($loselists as $loser){
                $message = \Swift_Message::newInstance($loser['username'] . ', Thank you for your participation')
                    ->setFrom($container->getParameter('mail_email'), 'ebid')
                    ->setTo(array($loser['email'] => $loser['username']))
                    ->setBody('Sorry, ' . $loser['username'] .', Thank you for your participation. You didn\'t win the product '. $loser['pname']);

                $mailer = $container->get('swiftMailer');
                $result = $mailer->send($message);
            }
        }

    }
} 