# Referral System

The referral system is designed for IT companies that want to reward customer-led growth without adding an external plugin.

## Core Features

- Unique referral code per customer
- Referral link generation
- Signup attribution from referral code
- Reward balance tracking
- Admin payout status controls
- Referral rules stored in settings

## Customer Flow

1. A customer account is created
2. A referral record and unique referral code are created automatically
3. The customer can share their referral link from the dashboard
4. New registrations can submit a referral code during signup
5. The sponsor account receives signup credit and reward balance

## Admin Flow

Use:

- `/admin/marketing/referrals`

Admin can:

- review all referral accounts
- see total signups and earned balances
- configure reward amount and payout threshold
- update payout status and internal notes

## Settings Keys

- `referral_enabled`
- `referral_reward_amount`
- `referral_percentage`
- `referral_minimum_payout`

## Schema

The referral table is ensured at runtime by `App\Core\SystemHooks::boot()` and is also included in the installation SQL.
