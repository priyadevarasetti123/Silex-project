<?php


namespace TwitterClone;

Class Authorization {


    # check the user login and return array
    public function inspectAuthorization(Tweets $application, $request)
    {
        $user = $application["Db"]->prepare("SELECT userid FROM users WHERE userid = ?  AND password = ?");

        $user->Execute(array($request->get('username'), $request->get('password')));


        return $user->fetch(\PDO::FETCH_ASSOC);
    }
}