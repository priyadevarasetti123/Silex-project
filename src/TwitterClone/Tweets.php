<?php

namespace TwitterClone;

use Silex\Application as SilexApplication;

# class Tweets

class Tweets extends SilexApplication
{
    # get the tweets of a user

    public function getPosts($userid)
    {
        $qry = $this["Db"]->prepare(
            "SELECT p.id, p.userid, post, p.created FROM posts p
             LEFT JOIN users u ON u.userid = p.userid
              WHERE u.userid = ?
              ORDER BY created DESC");

        $qry->Execute(array($userid));

        return $qry->fetchAll(\PDO::FETCH_ASSOC);
    }

    
    # save the tweet
    
    public function savePost($post, $userid)
    {
        $qry = $this["Db"]->prepare("INSERT INTO posts SET userid = ? , post = ? , created = ?");

        return $qry->Execute(array($userid, $post, time()));
    }

    # delete the tweet
    
    public function deletePost($id)
    {
        $qry = $this["Db"]->prepare("DELETE FROM posts WHERE id = ?");

        return $qry->Execute(array($id));
    }
}