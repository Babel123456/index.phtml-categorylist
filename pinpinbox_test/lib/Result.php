<?php

namespace Lib;

class Result
{
    const
        SYSTEM_ERROR = 'SYSTEM_ERROR',
        SYSTEM_OK = 'SYSTEM_OK',
        TOKEN_ERROR = 'TOKEN_ERROR',
        USER_ERROR = 'USER_ERROR',
        USER_EXISTS = 'USER_EXISTS',
        USER_NOTICE = 'USER_NOTICE',
        USER_REQUEST_USERLOGIN = 'USER_REQUEST_USERLOGIN',
        USER_WARNING = 'USER_WARNING';

    /**
     * For pinpinbox
     */
    const
        PHOTOUSEFOR_HAS_EXPIRED = 'PHOTOUSEFOR_HAS_EXPIRED',
        PHOTOUSEFOR_HAS_SENT_FINISHED = 'PHOTOUSEFOR_HAS_SENT_FINISHED',
        PHOTOUSEFOR_NOT_YET_STARTED = 'PHOTOUSEFOR_NOT_YET_STARTED',
        PHOTOUSEFOR_USER_HAS_EXCHANGED = 'PHOTOUSEFOR_USER_HAS_EXCHANGED',
        PHOTOUSEFOR_USER_HAS_GAINED = 'PHOTOUSEFOR_USER_HAS_GAINED',
        PHOTOUSEFOR_USER_HAS_SLOTTED = 'PHOTOUSEFOR_USER_HAS_SLOTTED',
        USER_OWNS_THE_ALBUM = 'USER_OWNS_THE_ALBUM';
}