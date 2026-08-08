<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class RoomBookingsExport implements FromCollection, WithHeadings, WithMapping
{
  protected $bookings;

  public function __construct($bookings)
  {
    $this->bookings = $bookings;
  }

  /**
   * @return \Illuminate\Support\Collection
   */
  public function collection()
  {
    return $this->bookings;
  }

  public function headings(): array
  {
    return [
      'Booking No.',
      'Customer Name',
      'Customer Number',
      'Customer Email',
      'Start Date',
      'End Date',
      'Price',
      'Discount',
      'Grand Total',
      'Paid via',
      'Payment Status',
      'Booking Date'
    ];
  }

  /**
   * @var $booking
   */
  public function map($booking): array
  {
    $symbol = $booking->currency_symbol ?? $booking->currency_text ?? '';
    $position = $booking->currency_symbol_position ?? $booking->currency_text_position ?? 'left';

    $totalRentValue = $booking->total_rent ?? $booking->subtotal;

    // price
    if (is_null($totalRentValue)) {
      $price = 'Requested';
    } else {
      $price = ($position == 'left' ? $symbol . ' ' : '') . $totalRentValue . ($position == 'right' ? ' ' . $symbol : '');
    }

    // discount
    if (is_null($booking->discount)) {
      $discount = '-';
    } else {
      $discount = ($position == 'left' ? $symbol . ' ' : '') . $booking->discount . ($position == 'right' ? ' ' . $symbol : '');
    }


    // grand total
    if (is_null($booking->grand_total)) {
      $grandTotal = '-';
    } else {
      $grandTotal = ($position == 'left' ? $symbol . ' ' : '') . $booking->grand_total . ($position == 'right' ? ' ' . $symbol : '');
    }

    $paymentStatus = match ((int) $booking->payment_status) {
      1 => 'Complete',
      3 => 'Partial',
      default => 'In Complete',
    };


    return [
      '#' . $booking->booking_number,
      $booking->customer_name,
      $booking->customer_phone,
      $booking->customer_email,
      $booking->startDate,
      $booking->endDate,
      $price,
      $discount,
      $grandTotal,
      is_null($booking->payment_method) ? '-' : $booking->payment_method,
      $paymentStatus,
      $booking->createdAt
    ];
  }
}
