<?php

namespace InstagramAPI;

class V2InboxResponse extends Response
{
    public $pending_requests_total;
    /**
     * @var string
     */
    public $seq_id;
    public $pending_requests_users;
    /**
     * @var Inbox
     */
    public $inbox;
    public $subscription;
}
