<?php

namespace App\Domain\Payment\Enums;

enum PaymentStatus:string
{
    case Pending='pending';

    case Success='success';

    case Failed='failed';

    case Cancelled='cancelled';
}

