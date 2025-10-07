<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum TransactionType: string implements HasColor, HasIcon, HasLabel
{
    case DEPOSIT = 'deposit';
    case WITHDRAW = 'withdraw';
    case LENDING_INTEREST_ACCRUED = 'lending interest accrued';
    case COLLECT_FEES = 'collect fees';
    case BORROW_PRINCIPAL = 'borrow_principal';
    case LOAN_INTEREST_ACCRUED = 'loan_interest_accrued';
    case REPAY_PRINCIPAL = 'repay_principal';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabel(): ?string
    {
        return match ($this) {
            self::DEPOSIT => 'Депозит',
            self::WITHDRAW => 'Вывод',
            self::LENDING_INTEREST_ACCRUED => 'Проценты по лендингу',
            self::COLLECT_FEES => 'Вывод комиссии',
            self::BORROW_PRINCIPAL => 'Взятие Займа',
            self::LOAN_INTEREST_ACCRUED => 'Проценты по займу',
            self::REPAY_PRINCIPAL => 'Погашение Займа',
        };
    }

    public function getIcon(): ?string
    {
        return match ($this) {
            self::DEPOSIT => 'heroicon-o-arrow-down-circle',
            self::WITHDRAW => 'heroicon-o-arrow-up-circle',
            self::LENDING_INTEREST_ACCRUED => 'heroicon-o-banknotes',
            self::COLLECT_FEES => 'heroicon-o-banknotes',
            self::BORROW_PRINCIPAL => 'heroicon-o-arrow-down-left',
            self::LOAN_INTEREST_ACCRUED => 'heroicon-o-arrow-trending-down',
            self::REPAY_PRINCIPAL => 'heroicon-o-minus-circle',
        };
    }

    public function getColor(): ?string
    {
        return match ($this) {
            self::DEPOSIT => 'success',
            self::WITHDRAW => 'danger',
            self::LENDING_INTEREST_ACCRUED => 'text-green-500',
            self::COLLECT_FEES => 'gray',
            self::BORROW_PRINCIPAL => 'text-orange-500',
            self::LOAN_INTEREST_ACCRUED => 'text-red-500',
            self::REPAY_PRINCIPAL => 'text-blue-500',
        };
    }
}
