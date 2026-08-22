# Requirements Document — E-Commerce Presale System

## Introduction

The E-Commerce Presale System is an Indonesian e-commerce platform designed for ordering essential goods (rice, fish, eggs, etc.) through shared links distributed via WhatsApp or Instagram. The system enables buyers to access a product catalog, manage shopping carts, select delivery options (COD pickup or home delivery), and pay via QRIS. After successful payment, buyers receive WhatsApp notifications confirming their order. Admins can manage product flash sales, inventory quotas, COD slots, monitor orders, and track affiliate referrals.

The system supports:
- **Product Management**: Catalog with flash sales, quotas, and purchase limits
- **Shopping Cart**: Add/remove products with real-time price updates
- **Delivery Options**: COD pickup at scheduled locations or home delivery with calculated shipping costs
- **Payment Processing**: QRIS integration with automatic refunds for unfulfilled orders
- **Notifications**: WhatsApp confirmations and order status updates
- **Admin Dashboard**: Product, quota, COD slot, and order management
- **Affiliate System**: Referral tracking and commission calculation

## Glossary

- **Buyer**: An end user who accesses the system to purchase products
- **Admin**: An authorized user who manages products, quotas, COD slots, and orders
- **System**: The E-Commerce Presale System
- **Product**: An item available for sale (e.g., rice, fish, eggs)
- **Flash Sale**: A time-limited promotion with specific products, pricing, and quantity limits
- **Quota**: The maximum number of units available for a product or flash sale
- **Cart**: A temporary storage area where buyers collect products before checkout
- **COD Slot**: A scheduled pickup location with specified date, time, and capacity
- **QRIS**: Quick Response Code for Indonesian Standards; a contactless payment method
- **Refund**: Return of payment to buyer if order cannot be fulfilled
- **Affiliate**: A partner who refers buyers and earns commissions on their purchases
- **WhatsApp Notification**: Automated message sent to buyer via WhatsApp for order confirmations and updates
- **Order**: A record of products purchased by a buyer with payment and delivery details
- **Stok**: The current available inventory for a product
- **Reservation**: Temporary allocation of inventory to a buyer during checkout (expires after 10 minutes)
- **Geolocation**: The buyer's home location pinpointed on a map for delivery cost calculation
- **OpenStreetMap**: Open-source mapping service used for location selection and distance calculation
- **Phone Validation**: Verification that a WhatsApp number follows the format: 10+ digits starting with 08 or +62

---

## Requirements

### Requirement 1.1 — Product Catalog Display

**User Story:** As a buyer, I want to view the available product catalog with remaining quotas, so that I can select and order the products I need.

#### Acceptance Criteria

1. WHEN a buyer opens the catalog link THEN the System SHALL display a list of active products with product name, product image, unit price, remaining quota, and purchase limit per buyer.
2. WHEN remaining quota for a product reaches zero THEN the System SHALL display status "Habis" (sold out) and disable the "Add to Cart" button for that product.
3. WHEN a flash sale has not started or has ended THEN the System SHALL display countdown timer if upcoming or final status if ended.
4. IF a product has a purchase limit (e.g., maximum 4 units per buyer) THEN the System SHALL restrict quantity input to not exceed the configured maximum.
5. WHEN the catalog page loads from a WhatsApp or Instagram link on a mobile device THEN the System SHALL render the page within 2 seconds with full responsiveness.

---

### Requirement 1.2 — Flash Sale Configuration

**User Story:** As an admin, I want to create and manage flash sales with product details, pricing, and time windows, so that I can run promotional campaigns effectively.

#### Acceptance Criteria

1. WHEN an admin creates a new flash sale THEN the System SHALL accept configuration of: product list, sale price, quota per product, purchase limit per buyer, start date/time, and end date/time.
2. WHEN a flash sale is active THEN the System SHALL apply the configured sale price instead of the regular price for all listed products.
3. WHEN a flash sale end time is reached THEN the System SHALL automatically deactivate the sale and revert to regular prices.
4. WHEN flash sale quota for a product is exhausted THEN the System SHALL prevent further purchases and update the status to sold out.

---

### Requirement 2.1 — Shopping Cart Management

**User Story:** As a buyer, I want to add, modify, and remove products from my cart before checkout, so that I can organize my order before purchase.

#### Acceptance Criteria

1. WHEN a buyer clicks "Add to Cart" on a product THEN the System SHALL add the product to the cart and update the cart item count badge.
2. WHEN a buyer opens the cart THEN the System SHALL display: list of products, quantity per product, unit price, item subtotal, and cart total price.
3. WHEN a buyer modifies the quantity of an item in the cart THEN the System SHALL update the item subtotal and cart total price in real time.
4. WHEN a buyer removes an item from the cart THEN the System SHALL delete the item and recalculate the cart total price.
5. IF a buyer attempts to add quantity exceeding the product's purchase limit THEN the System SHALL display an error message and prevent the addition.
6. IF the buyer's cart remains inactive for 30 minutes THEN the System SHALL retain the cart without clearing it.

---

### Requirement 2.2 — Cart Persistence

**User Story:** As a buyer, I want my cart to be preserved across browser sessions, so that I don't lose my items if I close and reopen the browser.

#### Acceptance Criteria

1. WHEN a buyer adds items to the cart THEN the System SHALL store the cart data in the browser's local storage.
2. WHEN a buyer returns to the system in a new session THEN the System SHALL restore the previous cart contents.
3. WHEN a buyer proceeds to checkout THEN the System SHALL clear the saved cart data after order confirmation.

---

### Requirement 3.1 — Buyer Information Collection

**User Story:** As a buyer, I want to enter my details during checkout, so that the admin knows my identity and can contact me if needed.

#### Acceptance Criteria

1. WHEN a buyer proceeds to checkout from the cart THEN the System SHALL display a form with required fields: full name and WhatsApp phone number.
2. WHEN a buyer enters a WhatsApp phone number THEN the System SHALL validate the format: minimum 10 digits, starting with 08 or +62.
3. IF the phone number format is invalid THEN the System SHALL display an error message and prevent form submission.
4. WHEN a buyer submits the form THEN the System SHALL validate that all required fields are populated.
5. WHEN form validation succeeds THEN the System SHALL proceed to the delivery method selection step.

---

### Requirement 3.2 — Inventory Reservation

**User Story:** As a buyer, I want to ensure my selected products are reserved during checkout, so that the items don't get sold to someone else while I'm completing payment.

#### Acceptance Criteria

1. WHEN a buyer submits buyer information THEN the System SHALL create a temporary inventory reservation for all cart items.
2. WHEN inventory is reserved THEN the System SHALL mark the reserved quantity as unavailable to other buyers for 10 minutes.
3. WHEN 10 minutes elapse without payment confirmation THEN the System SHALL release the reservation and make inventory available again.
4. WHEN a buyer completes payment THEN the System SHALL convert the reservation to a permanent deduction from inventory.

---

### Requirement 4.1 — COD Pickup Option

**User Story:** As a buyer, I want to select a pickup location and time slot for cash-on-delivery, so that I can collect my order conveniently.

#### Acceptance Criteria

1. WHEN a buyer selects the delivery method THEN the System SHALL present two options: "Ambil di Lokasi COD" (pickup at location) and "Antar ke Rumah" (home delivery).
2. WHEN a buyer chooses "Ambil di Lokasi COD" THEN the System SHALL display available COD locations with address, day of week, time window (e.g., Monday 13:00–14:00), and available capacity.
3. WHEN a COD slot reaches maximum capacity THEN the System SHALL disable that slot and prevent selection.
4. WHEN a buyer selects a COD slot THEN the System SHALL display an order summary: product list, quantities, subtotal, COD location, scheduled date, and time window.
5. WHEN the buyer confirms the COD selection THEN the System SHALL proceed to payment.

---

### Requirement 4.2 — COD Slot Management

**User Story:** As an admin, I want to create and manage COD pickup locations and time slots, so that I can organize order fulfillment logistics.

#### Acceptance Criteria

1. WHEN an admin creates a COD slot THEN the System SHALL accept: location name, address, day of week, start time, end time, and maximum capacity.
2. WHEN an admin updates a COD slot capacity THEN the System SHALL reflect the change and prevent new reservations if capacity is exceeded.
3. WHEN a COD slot is booked by a buyer THEN the System SHALL increment the reservation count and update available capacity.
4. WHEN an admin deactivates a COD slot THEN the System SHALL prevent new bookings for that slot.

---

### Requirement 4.3 — Home Delivery with Distance Calculation

**User Story:** As a buyer, I want to select home delivery and see the calculated shipping cost based on distance, so that I know the total cost before payment.

#### Acceptance Criteria

1. WHEN a buyer chooses "Antar ke Rumah" (home delivery) THEN the System SHALL display an interactive map powered by OpenStreetMap.
2. WHEN a buyer pins their home location on the map THEN the System SHALL record the buyer's geolocation coordinates.
3. WHEN a buyer confirms the location THEN the System SHALL calculate the distance from the nearest COD location to the buyer's home.
4. WHEN distance is calculated THEN the System SHALL apply the configured shipping rate per kilometer to compute shipping cost.
5. WHEN shipping cost is calculated THEN the System SHALL display the order summary: product subtotal, shipping cost, and total amount due.
6. IF the buyer's location exceeds the maximum delivery radius configured by the admin THEN the System SHALL display "Lokasi di luar jangkauan pengiriman" (location out of delivery range) and prevent order placement.

---

### Requirement 4.4 — Shipping Rate Configuration

**User Story:** As an admin, I want to set the per-kilometer shipping rate and maximum delivery radius, so that I can control delivery costs and coverage.

#### Acceptance Criteria

1. WHEN an admin accesses shipping settings THEN the System SHALL display fields for: shipping rate per kilometer and maximum delivery radius in kilometers.
2. WHEN an admin updates shipping rate THEN the System SHALL apply the new rate to all new orders from that point forward.
3. WHEN an admin updates maximum delivery radius THEN the System SHALL enforce the limit on subsequent home delivery orders.

---

### Requirement 5.1 — QRIS Payment Processing

**User Story:** As a buyer, I want to pay for my order using QRIS, so that the transaction is convenient and cashless.

#### Acceptance Criteria

1. WHEN a buyer confirms their order THEN the System SHALL display the QRIS code with the exact amount due and a 10-minute payment deadline.
2. WHEN the buyer scans and completes the QRIS payment THEN the System SHALL receive payment confirmation from the payment gateway.
3. WHEN payment is confirmed THEN the System SHALL update the order status to "Dibayar" (paid) and permanently deduct inventory from quota.
4. WHEN 10 minutes elapse without payment confirmation THEN the System SHALL automatically cancel the order, release inventory reservation, and update order status to "Dibatalkan" (cancelled).
5. IF payment fails THEN the System SHALL display "Pembayaran Gagal" (payment failed) and offer the option to retry.
6. IF a race condition occurs (stock depleted after payment but before inventory update) THEN the System SHALL mark the order as "Tidak Terpenuhi" (unfulfilled) and initiate an automatic refund.

---

### Requirement 5.2 — Payment Gateway Integration

**User Story:** As the system, I need to integrate with a QRIS payment gateway to process payments securely and receive confirmations.

#### Acceptance Criteria

1. WHEN the System generates a QRIS payment request THEN it SHALL send the order ID, amount, and buyer phone number to the payment gateway API.
2. WHEN the payment gateway receives a payment confirmation THEN the System SHALL validate the payment signature for security.
3. WHEN payment signature validation succeeds THEN the System SHALL mark the order as paid.
4. WHEN the System receives a webhook from the payment gateway THEN it SHALL update the corresponding order record.

---

### Requirement 6.1 — WhatsApp Notification on Payment Success

**User Story:** As a buyer, I want to receive a WhatsApp confirmation message after successful payment, so that I have proof of my order.

#### Acceptance Criteria

1. WHEN payment is successfully confirmed THEN the System SHALL send a WhatsApp message to the buyer's phone number with: order confirmation, product list with quantities, total amount paid, COD location and schedule (if pickup), or delivery address (if home delivery), and estimated delivery date/time.
2. WHEN the WhatsApp message is successfully delivered THEN the System SHALL record the notification status as "Terkirim" (sent) in the order record.
3. IF WhatsApp message delivery fails THEN the System SHALL record the failure status and flag the order for manual admin follow-up.
4. WHEN an admin manually resends a notification THEN the System SHALL queue the message and attempt delivery with retry logic.

---

### Requirement 6.2 — WhatsApp Notification on Order Cancellation

**User Story:** As a buyer, I want to be notified via WhatsApp if my order is cancelled, so that I'm immediately aware of any issues.

#### Acceptance Criteria

1. WHEN an order is cancelled (e.g., timeout or unfulfilled) THEN the System SHALL send a WhatsApp message to the buyer with: order ID, cancellation reason, and (if applicable) refund amount and estimated refund timeline.
2. WHEN cancellation notification is sent THEN the System SHALL record the notification event in the order history.

---

### Requirement 6.3 — WhatsApp API Integration

**User Story:** As the system, I need to send WhatsApp messages reliably through an API provider.

#### Acceptance Criteria

1. WHEN the System queues a WhatsApp message THEN it SHALL connect to the WhatsApp API provider and submit the message with recipient phone number and content.
2. WHEN the API returns a delivery confirmation THEN the System SHALL update the notification status in the order record.
3. WHEN the API returns a delivery failure THEN the System SHALL record the failure and log the error message for admin review.

---

### Requirement 7.1 — Order Confirmation Page

**User Story:** As a buyer, I want to see a confirmation page after payment, so that I can verify my order details and save the information.

#### Acceptance Criteria

1. WHEN payment is successfully completed THEN the System SHALL display a confirmation page with: unique order number, product list with quantities, total amount paid, COD location and pickup schedule (or delivery address and estimated arrival), and timestamp of order placement.
2. WHEN the confirmation page is displayed THEN the System SHALL provide buttons to: share order details, print the page, or return to the home page.
3. WHEN a buyer accesses their order using the order number THEN the System SHALL display the current order status and estimated fulfillment date.

---

### Requirement 7.2 — Order Status Tracking

**User Story:** As a buyer, I want to check my order status anytime using my order number, so that I know when to pick up or expect delivery.

#### Acceptance Criteria

1. WHEN a buyer enters an order number on the tracking page THEN the System SHALL retrieve and display the order if it exists.
2. WHEN order status is retrieved THEN the System SHALL display current status, products ordered, scheduled pickup/delivery details, and payment confirmation.
3. IF the order number is not found THEN the System SHALL display "Pesanan tidak ditemukan" (order not found).

---

### Requirement 8.1 — Automatic Refund Processing

**User Story:** As a buyer, I want to receive a refund if my ordered items are not available, so that my money is returned.

#### Acceptance Criteria

1. WHEN an order is marked as "Tidak Terpenuhi" (unfulfilled) THEN the System SHALL initiate automatic refund processing.
2. WHEN refund is initiated THEN the System SHALL update the order status to "Refund Diproses" (refund processing) and record the refund amount.
3. WHEN refund is processed THEN the System SHALL send a WhatsApp message to the buyer confirming the refund with: refund amount and estimated time to receive funds (typically 1–3 business days).
4. IF automatic refund cannot be processed due to payment gateway errors THEN the System SHALL flag the order as "Refund Manual Diperlukan" (manual refund required) for admin intervention.

---

### Requirement 8.2 — Admin Refund Management

**User Story:** As an admin, I want to manually process refunds if automatic processing fails, so that I can resolve payment issues quickly.

#### Acceptance Criteria

1. WHEN an admin views an order flagged for manual refund THEN the System SHALL display order details and a "Proses Refund Manual" button.
2. WHEN an admin initiates manual refund THEN the System SHALL open a form to confirm: order ID, refund amount, and refund method.
3. WHEN manual refund is submitted THEN the System SHALL record the refund transaction and update the order status to "Refund Berhasil" (refund successful).
4. WHEN refund is completed THEN the System SHALL notify the buyer via WhatsApp.

---

### Requirement 9.1 — Admin Dashboard — Order Management

**User Story:** As an admin, I want to view and manage all incoming orders, so that I can track sales and order fulfillment.

#### Acceptance Criteria

1. WHEN an admin opens the dashboard THEN the System SHALL display a list of all orders with: order number, buyer name, order date, status, total amount, and delivery method.
2. WHEN an admin filters orders by status (e.g., "Pending", "Dibayar", "Siap COD", "Selesai", "Dibatalkan") THEN the System SHALL display only orders matching the selected status.
3. WHEN an admin searches for an order by order number or buyer name THEN the System SHALL return matching orders.
4. WHEN an admin clicks on an order THEN the System SHALL display full order details: buyer name, phone, products, quantities, prices, delivery address/COD slot, and payment receipt.
5. WHEN an admin updates an order status THEN the System SHALL record the timestamp and send a WhatsApp notification to the buyer (unless the status is "Pending").

---

### Requirement 9.2 — Admin Dashboard — Product & Quota Management

**User Story:** As an admin, I want to manage product inventory and flash sale quotas, so that I can control stock levels and sales campaigns.

#### Acceptance Criteria

1. WHEN an admin accesses product management THEN the System SHALL display all products with: product name, image, current price, current inventory, purchase limit per buyer, and status (active/inactive).
2. WHEN an admin creates a new product THEN the System SHALL accept: product name, description, image, price, inventory quantity, and purchase limit per buyer.
3. WHEN an admin edits a product THEN the System SHALL allow modification of: price, inventory, purchase limit, and status.
4. WHEN an admin deactivates a product THEN the System SHALL prevent it from appearing in the catalog and block new purchases.
5. WHEN an admin creates a flash sale THEN the System SHALL configure: sale products, sale price, sale quota per product, purchase limit per buyer, and sale start/end date and time.
6. WHEN a flash sale quota is reached THEN the System SHALL automatically mark the product as sold out within that sale.

---

### Requirement 9.3 — Admin Dashboard — COD Slot Management

**User Story:** As an admin, I want to create and manage COD pickup slots, so that I can organize order collection logistics.

#### Acceptance Criteria

1. WHEN an admin accesses COD management THEN the System SHALL display all COD slots with: location name, address, day of week, time window, maximum capacity, and current reservations.
2. WHEN an admin creates a new COD slot THEN the System SHALL accept: location name, address, day of week, start time, end time, and maximum capacity.
3. WHEN an admin edits a COD slot THEN the System SHALL allow modification of: address, time window, capacity, and status (active/inactive).
4. WHEN an admin deactivates a COD slot THEN the System SHALL prevent new bookings for that slot and display it as unavailable to buyers.
5. WHEN an admin views a COD slot THEN the System SHALL display the current reservation count and list of orders assigned to that slot.

---

### Requirement 9.4 — Admin Dashboard — Shortlink Generation

**User Story:** As an admin, I want to generate short links for flash sales, so that I can easily share them via WhatsApp and Instagram.

#### Acceptance Criteria

1. WHEN an admin creates a flash sale THEN the System SHALL automatically generate a unique, shareable short URL for that sale.
2. WHEN an admin views a flash sale THEN the System SHALL display the short link with a copy-to-clipboard button.
3. WHEN a buyer opens the short link THEN the System SHALL redirect to the catalog page with that flash sale's products highlighted or pre-selected.
4. WHEN an admin tracks a short link THEN the System SHALL display: number of times the link was accessed and number of orders generated from that link.

---

### Requirement 10.1 — Affiliate System — Referral Link Generation

**User Story:** As an affiliate, I want to generate a unique referral link, so that I can track buyers I refer and earn commissions.

#### Acceptance Criteria

1. WHEN an affiliate registers THEN the System SHALL generate a unique affiliate ID and provide a base referral link.
2. WHEN an affiliate appends their ID to the system URL THEN any buyer accessing the system through that link SHALL be tagged with the affiliate ID.
3. WHEN a buyer completes a purchase through an affiliate link THEN the System SHALL record the affiliate ID on the order record.
4. WHEN an affiliate views their dashboard THEN the System SHALL display their unique referral link with a copy-to-clipboard button.

---

### Requirement 10.2 — Affiliate System — Commission Calculation

**User Story:** As an affiliate, I want to see my earned commissions based on successful referrals, so that I can track my earnings.

#### Acceptance Criteria

1. WHEN an order is completed (paid and recorded) THEN the System SHALL calculate the affiliate commission as a percentage of the order total (rate configured by admin).
2. WHEN commission is calculated THEN the System SHALL record it as pending until the order is fulfilled (status "Selesai").
3. WHEN an order is marked as "Selesai" (completed) THEN the System SHALL confirm the commission and update the affiliate's total earned balance.
4. WHEN an affiliate views their dashboard THEN the System SHALL display: total referrals, total sales amount from referrals, current pending commission, total earned commission, and payout status.

---

### Requirement 10.3 — Affiliate System — Commission Payout

**User Story:** As an affiliate, I want to request a payout of my earned commissions, so that I can receive my earnings.

#### Acceptance Criteria

1. WHEN an affiliate's earned balance reaches the minimum payout threshold (configured by admin) THEN the System SHALL enable the "Request Payout" button.
2. WHEN an affiliate requests a payout THEN the System SHALL accept: payout method (e.g., bank transfer) and payment details.
3. WHEN a payout request is submitted THEN the System SHALL update the affiliate's balance to show pending payout and send confirmation via WhatsApp.
4. WHEN an admin approves a payout THEN the System SHALL process the transfer and notify the affiliate.
5. IF a payout cannot be processed THEN the System SHALL revert the funds to the affiliate's available balance.

---

### Requirement 10.4 — Admin Affiliate Management

**User Story:** As an admin, I want to manage affiliate accounts and commission rates, so that I can oversee the affiliate program.

#### Acceptance Criteria

1. WHEN an admin accesses affiliate management THEN the System SHALL display all registered affiliates with: affiliate name, ID, total referrals, total commission earned, and current balance.
2. WHEN an admin configures affiliate commission rate THEN the System SHALL accept a percentage rate applied to all new orders.
3. WHEN an admin sets the minimum payout threshold THEN the System SHALL apply it when affiliates request payouts.
4. WHEN an admin views an affiliate's details THEN the System SHALL display: referral link, list of referred buyers, commission history, and payout history.
5. WHEN an admin deactivates an affiliate THEN the System SHALL prevent new referrals through that affiliate's link.

---

### Requirement 11.1 — Data Validation and Security

**User Story:** As the system, I need to validate and secure all user inputs to prevent errors and attacks.

#### Acceptance Criteria

1. WHEN a buyer submits any form THEN the System SHALL validate all required fields are populated and match expected formats.
2. WHEN a buyer enters a phone number THEN the System SHALL validate format before accepting: minimum 10 digits, starting with 08 or +62.
3. WHEN a buyer enters a geolocation on the map THEN the System SHALL validate that coordinates are within reasonable geographic bounds.
4. WHEN the System receives external data (payment gateway responses, API calls) THEN it SHALL sanitize and validate data before processing.
5. WHEN the System stores sensitive data (phone numbers, order details) THEN it SHALL use encryption at rest.

---

### Requirement 11.2 — Concurrent Access and Race Condition Handling

**User Story:** As the system, I need to handle multiple simultaneous purchases safely to prevent inventory overselling.

#### Acceptance Criteria

1. WHEN multiple buyers attempt to purchase the same product simultaneously THEN the System SHALL use inventory locking to prevent overselling.
2. WHEN a buyer reserves inventory during checkout THEN the System SHALL mark reserved items as temporarily unavailable to other buyers.
3. WHEN payment confirmation arrives THEN the System SHALL atomically commit the inventory deduction.
4. IF two buyers complete payment for the same last item simultaneously (race condition) THEN the System SHALL fulfill the first order and cancel the second with automatic refund.

---

### Requirement 11.3 — System Performance and Availability

**User Story:** As the system, I need to remain responsive and available during peak sales periods.

#### Acceptance Criteria

1. WHEN the catalog page is accessed THEN the System SHALL respond and render within 2 seconds on a 4G mobile connection.
2. WHEN multiple buyers are checking out simultaneously THEN the System SHALL handle at least 100 concurrent checkout sessions without degradation.
3. WHEN a flash sale starts THEN the System SHALL handle traffic spike without downtime or errors.
4. WHEN the System experiences an internal error THEN it SHALL return a meaningful error message to the user within 5 seconds.

