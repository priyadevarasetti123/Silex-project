<?php

# Twitter clone application using Silex

use Symfony\Component\HttpFoundation\Request;
use TwitterClone\Tweets;
use TwitterClone\Db\DbServiceProvider;
use Silex\Provider\AssetServiceProvider;

require_once __DIR__.'/../vendor/autoload.php';

$application = new TwitterClone\Tweets();


$application['debug'] = true;

$request = Request::create($_SERVER['REQUEST_URI'], 'REQUEST');


# service injection

$application['user.auth'] = function ($application) {
    return new TwitterClone\Authorization($application);
};

$application['twitter.tweets'] = $application->protect(function (Tweets $application, $userID)    {

    return $application->getPosts($userID);
});


# service provider and database

$application->register(new DbServiceProvider(),
    array(
        'Db.dsn' => 'mysql:host=den1.mysql3.gear.host;dbname=twitter',
        'Db.username' => 'twitter',
        'Db.password' => 'Az2n-5CVc0l?',
    )
);

# service provider and assets
$application->register(new AssetServiceProvider());

# service provider and twig templates
$application->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/../templates',
));

# service provider and session data
$application->register(new Silex\Provider\SessionServiceProvider());



# login
$application->get('/login', function (Request $request) use ($application) {

    if( $user = $application['user.auth']->inspectAuthorization($application, $request)) {

        $application['session']->set('message', '');
        $application['session']->set('user', array('username' => $user['userid']));

        return $application->redirect('/account');
    }

    $application['session']->set('message', 'The login credentials are not correct');

    return $application->redirect('/');

})->method('POST');


# logout
$application->get('/logout', function (Request $request) use ($application) {

    $application['session']->set('user', array());
    $application['session']->invalidate(1);

    return $application->redirect('/');
});

# user account
$application->get('/account', function () use ($application) {
    if (!$user = $application['session']->get('user')) {
        return $application->redirect('/');
    }

    return $application['twig']->render('tweetPage.html.twig', array(
        'name' => $user['username'],
        'tweets' => $application['twitter.tweets']($application, $user['username'])
    ));
});


# tweet
$application->get('/tweet', function (TwitterClone\Tweets $application , Request $request)  {
    $user = $application['session']->get('user');
    $post = $request->get('chars'); 
    
    if($application->savePost($post, $user['username'])) {

        return $application['twig']->render('tweetPage.html.twig', array(
            'name' => $user['username'],
            'tweets' => $application['twitter.tweets']($application, $user['username'])
        ));
    } 
    

})->method('POST');

# delete
$application->get('/delete', function (TwitterClone\Tweets $application , Request $request)  {
    $user = $application['session']->get('user');
    $id =$_GET['id'];
    if($application->deletePost($id)) {

        return $application['twig']->render('tweetPage.html.twig', array(
            'name' => $user['username'],
            'tweets' => $application['twitter.tweets']($application, $user['username'])
        ));
    }

})->method('POST');


# front page
$application->get('/', function (TwitterClone\Tweets $application , Request $request)  {

    return $application['twig']->render('frontPage.html.twig', array(
        'message' => $application['session']->get('message')
    ));
});


$application->run();



