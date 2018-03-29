<?php

namespace Smartling\Jobs;


class JobStatus
{
    const AWAITING_AUTHORIZATION    = 'AWAITING_AUTHORIZATION';
    const IN_PROGRESS               = 'IN_PROGRESS';
    const COMPLETED                 = 'COMPLETED';
    const CANCELLED                 = 'CANCELLED';
    const CLOSED                    = 'CLOSED';
}
