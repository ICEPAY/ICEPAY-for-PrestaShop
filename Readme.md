# ICEPAY Integration Module for PrestaShop

This module integrates the ICEPAY payment gateway into your PrestaShop store. It allows customers to complete transactions using ICEPAY's secure checkout and supports webhook and redirect flows.

## Features

- Seamless ICEPAY payment integration
- Support for multiple payment methods
- Automatic order validation on return and webhook
- Custom transaction tracking
- Admin interface to view available payment methods
- Process refunds directly from PrestaShop

---

## Requirements

- PrestaShop 1.7+
- PHP 7.2 or higher
- An active [ICEPAY](https://www.icepay.com) merchant account

---

## Installation

1. Go to your PrestaShop back office.
2. Navigate to **Modules > Module Manager**.
3. Click **Upload a module** and select the `icepay.zip` archive.
4. After installation, go to **Configure**.

---

## Configuration

1. Enter your ICEPAY credentials (Merchant ID and Secret).
2. Select the payment methods to offer.
3. Set appropriate order statuses for successful, failed, or pending payments.
4. Save settings.

---

## How It Works

1. When a customer places an order, they are redirected to ICEPAY's checkout.
2. Upon completion, ICEPAY redirects the user back and notifies the module via a webhook.
3. The module verifies and validates the order, updating its status accordingly.

---

## Refunds
The module supports refunds through the PrestaShop back office:

- Go to **Orders > View Order**.
- In the order detail page, use the **Refund** button.
- The module will:
    - Call the ICEPAY refund API.
    - Update the order history with the refund status.
    - Log errors if the refund could not be processed.

Refunds can be either:
- **Full Refund**: Entire order amount is refunded.
- **Partial Refund**: Only a specific amount is refunded.

**Important:**  Cancelling an order does not trigger a refund

---

## Troubleshooting

- **Payments not validating:** Ensure webhooks are enabled in your ICEPAY dashboard.
- **Redirect fails:** Verify that your return URL is reachable and uses HTTPS.
- **"Undefined method" errors:** Check your PrestaShop version compatibility and clear the cache.

---

## Development & Contributions

This module follows PrestaShop’s development standards. Contributions, bug reports, and feature suggestions are welcome. Please open an issue or submit a pull request.
