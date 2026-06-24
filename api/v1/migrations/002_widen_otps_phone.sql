-- Migration: Widen otps.phone column to store email addresses
-- The forgot-password flow stores emails in this column, but VARCHAR(15)
-- is too small for most email addresses, causing truncation and OTP lookup failures.

ALTER TABLE `otps` MODIFY `phone` VARCHAR(255) NOT NULL;
