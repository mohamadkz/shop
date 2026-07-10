<?php

namespace App\Enums;

enum OrderStatus:string
{
    case Pending='pending';

    case Paid='paid';

    case Processing='processing';

    case Completed='completed';

    case Cancelled='cancelled';

    case Failed='failed';

    case Shipped='shipped';
}
