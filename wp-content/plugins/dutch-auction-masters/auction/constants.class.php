<?php
/**
 * Copyright (c) 2013 Second Company B.V. <support@dutchauctionmasters.com>
 * http://www.dutchauctionmasters.com/
 * All rights reserved.
 */

class DamStatus
{
    const AUTO_DRAFT = "auto-draft";
    const DRAFT = "draft";
    const PENDING = "pending";
    const PUBLISH = "	publish";
    const TRASH = "trash";
}

class AuctionType
{
    const COMMON = 0;
    const DUTCH_AUCTION = 1;
}

class AuctionStatus
{
    const DRAFT = 0;
    const NORMAL = 10;
    const UPCOMING = 10;
    const RUNNING = 10;
    const CLOSED = 10;
    const TRASH = 20;
}

class DealState
{
    const NONE = "";
    const WAIT_TO_PAY = "WAIT_TO_PAY";
    const PAID = "PAID";
    const PAYING = "PAYING";
    const DELIVERED = "DELIVERED";
    const ACCEPTED = "ACCEPTED";
    const PAID_OUT = "PAID_OUT";
    const DEADBEAT = "DEADBEAT";
}

class AuctionStrStatus
{
    const UPCOMING = 'upcoming';
    const RUNNING = 'running';
}

class PaymentMethod
{
    const OFFLINE = 'offline';
    const PAYPAL = 'paypal';
    const IDEAL = 'ideal';
    const BANK_TRANSFER = 'bank_transfer';
}