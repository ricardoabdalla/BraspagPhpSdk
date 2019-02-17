<?php

namespace BraspagSdk\Common;

abstract class OAuthGrantType
{
    const ClientCredentials = "client_credentials";

    const Password = "Password";

    const AuthorizationCode = "AuthorizationCode";

    const RefreshToken = "RefreshToken";
}
