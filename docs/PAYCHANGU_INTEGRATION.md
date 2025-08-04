# PayChangu Payment Gateway Integration

This document outlines the complete PayChangu payment gateway integration for the MO-Boards contract template marketplace.

## Overview

PayChangu is Malawi's leading online payment gateway that supports both mobile money and bank card payments. This integration allows companies to purchase professional contract templates using local payment methods.

## Features Implemented

### 1. PayChangu Service Layer
- **Location**: `app/Services/PayChangu/`
- **Components**:
  - `PayChanguService.php` - Main service class
  - `PayChanguPaymentRequest.php` - Payment request DTO
  - `PayChanguPaymentResponse.php` - Payment response DTO
  - `PayChanguPaymentVerification.php` - Payment verification DTO

### 2. Payment Flow
1. **Template Selection**: User browses marketplace and selects a template
2. **Payment Initiation**: Creates a payment transaction record
3. **Payment Checkout**: User selects payment method (Mobile Money or Bank Card)
4. **PayChangu Integration**: Redirects to PayChangu secure checkout
5. **Payment Processing**: PayChangu handles the actual payment
6. **Webhook Callback**: PayChangu notifies our system of payment status
7. **Purchase Completion**: Creates purchase record and grants template access

### 3. Database Schema

#### Template Payment Transactions Table
```sql
CREATE TABLE template_payment_transactions (
    id BIGINT PRIMARY KEY,
    company_id BIGINT,
    template_id BIGINT,
    user_id BIGINT,
    payment_id VARCHAR(255) UNIQUE,  -- PayChangu payment ID
    reference VARCHAR(255) UNIQUE,   -- Our internal reference
    amount DECIMAL(10,2),
    currency VARCHAR(3) DEFAULT 'MWK',
    status ENUM('pending', 'processing', 'paid', 'completed', 'failed', 'cancelled'),
    checkout_url VARCHAR(255),
    return_url VARCHAR(255),
    cancel_url VARCHAR(255),
    customer_details JSON,
    metadata JSON,
    payment_initiated_at TIMESTAMP,
    payment_completed_at TIMESTAMP NULL,
    payment_failed_at TIMESTAMP NULL,
    failure_reason VARCHAR(255) NULL,
    paychangu_response JSON NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### 4. Configuration

#### Environment Variables
Add these to your `.env` file:

```env
# PayChangu Configuration
PAYCHANGU_BASE_URL=https://api.paychangu.com
PAYCHANGU_CLIENT_ID=your_paychangu_client_id
PAYCHANGU_CLIENT_SECRET=your_paychangu_client_secret
PAYCHANGU_WEBHOOK_SECRET=your_webhook_secret
PAYCHANGU_CALLBACK_URL="${APP_URL}/contract-templates/payment/callback"
PAYCHANGU_CURRENCY=MWK
PAYCHANGU_ENVIRONMENT=sandbox
```

#### Configuration File
- **Location**: `config/paychangu.php`
- **Purpose**: Centralizes PayChangu settings

### 5. Controller Methods

#### ContractTemplateController New Methods:
- `purchase()` - Initiates payment process
- `processPayment()` - Handles PayChangu integration
- `paymentCallback()` - Processes webhook notifications

### 6. Frontend Components

#### PaymentCheckout.vue
- **Location**: `pages/contract-templates/PaymentCheckout.vue`
- **Features**:
  - Template details display
  - Payment method selection (Mobile Money, Bank Card)
  - Terms and conditions
  - Purchase summary
  - Secure payment processing

#### MarketPlace.vue
- **Location**: `pages/contract-templates/MarketPlace.vue`
- **Features**:
  - Template browsing and filtering
  - Category-based organization
  - Search functionality
  - Purchase and preview buttons
  - Responsive design

### 7. Payment Methods Supported

#### Mobile Money
- Airtel Money
- TNM Mpamba
- Real-time processing

#### Bank Cards
- Visa
- Mastercard
- Secure 3D processing

### 8. Security Features

#### Webhook Validation
- HMAC signature verification
- Payload integrity checking
- Replay attack prevention

#### Transaction Security
- Unique reference generation
- Status tracking
- Audit logging

### 9. Error Handling

#### Payment Failures
- Automatic retry mechanisms
- Clear error messaging
- Transaction status tracking
- Detailed logging

#### System Errors
- Graceful error handling
- User-friendly messages
- Admin notifications
- Recovery procedures

### 10. Testing

#### Sandbox Environment
- Test credentials provided by PayChangu
- Safe testing environment
- Mock payment scenarios

#### Test Cases
- Successful payments
- Failed payments
- Webhook processing
- Edge cases

## Implementation Status

✅ **Completed:**
- PayChangu service integration
- Database schema
- Payment flow implementation
- Webhook handling
- Frontend components
- Error handling
- Security measures

🔄 **Pending:**
- PayChangu API credentials setup
- Production environment configuration
- Live testing with real payments

## Next Steps

### 1. PayChangu Account Setup
1. Register with PayChangu
2. Obtain API credentials
3. Configure webhook endpoints
4. Test in sandbox environment

### 2. Production Deployment
1. Update environment variables
2. Switch to production endpoints
3. Configure SSL certificates
4. Test payment flows

### 3. Monitoring & Analytics
1. Payment success rates
2. Failed payment analysis
3. Revenue tracking
4. User behavior analytics

## API Integration Details

### Authentication
PayChangu uses OAuth 2.0 client credentials flow:
```php
POST /oauth/token
{
    "grant_type": "client_credentials",
    "client_id": "your_client_id",
    "client_secret": "your_client_secret",
    "scope": "payments"
}
```

### Payment Creation
```php
POST /api/v1/payments
Authorization: Bearer {access_token}
{
    "amount": 100.00,
    "currency": "MWK",
    "reference": "TMPL_ABC123_1234567890",
    "callback_url": "https://yourdomain.com/payment/callback",
    "return_url": "https://yourdomain.com/success",
    "cancel_url": "https://yourdomain.com/cancel",
    "customer": {
        "first_name": "John",
        "last_name": "Doe",
        "email": "john@example.com",
        "phone": "+265888123456"
    }
}
```

### Payment Verification
```php
GET /api/v1/payments/{payment_id}
Authorization: Bearer {access_token}
```

## Support & Documentation

### PayChangu Resources
- **Website**: https://paychangu.com
- **API Documentation**: https://docs.paychangu.com
- **Support**: support@paychangu.com

### Developer Resources
- **PayChangu Dashboard**: Access to transaction logs
- **Webhook Logs**: Real-time webhook delivery status
- **API Testing**: Sandbox environment for development

## Conclusion

This PayChangu integration provides a complete, secure, and user-friendly payment solution for the MO-Boards template marketplace. The implementation follows best practices for security, error handling, and user experience while leveraging Malawi's premier payment gateway for local market accessibility.
