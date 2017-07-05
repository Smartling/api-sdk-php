<?php

namespace Smartling\Jobs;


class JobStatus
{
    // const DRAFT                     = 'DRAFT';
    const AWAITING_AUTHORIZATION    = 'AWAITING_AUTHORIZATION';
    const IN_PROGRESS               = 'IN_PROGRESS';
    const COMPLETED                 = 'COMPLETED';
    // const REJECTED                  = 'REJECTED';
    const CANCELLED                 = 'CANCELLED';
}