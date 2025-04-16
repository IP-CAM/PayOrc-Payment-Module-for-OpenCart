# PayOrc Payment Module for OpenCart

  

## Overview

The **PayOrc Payment Module** enables merchants to accept payments via PayOrc's secure payment solutions. This module supports both **embedded** and **hosted** payment options and provides **manual** or **automatic** payment capture modes.

Signup for sandbox account: https://merchant.payorc.com/console/merchant-signup

Visit API documentation: https://api.payorc.com

## Features

- Supports **AUTH** and **SALE** transaction types

- Choose between **PayOrc Embedded Solution** or **PayOrc Hosted Solution**

- Supports **Manual** or **Automatic** payment capture

- Secure transaction processing with merchant credentials

  

## Installation

1. Download the `payorc` payment module.

2. Log in to your OpenCart Admin Panel.

3. Navigate to **Extensions > Extension Installer**.

4. Click **Upload** and select the downloaded `payorc` module ZIP file.

5. After the upload, go to **Extensions > Extensions**.

6. Under the **Payment** category, find **PayOrc** and click **Install**.

  

## Configuration

1. Navigate to **Extensions > Extensions** and select **Payment**.

2. Find **PayOrc** in the list and click **Edit**.

3. Fill in the required fields:

-  **Merchant Key**: `xyz`

-  **Merchant Secret**: Your secret key (keep this secure).

-  **Action**: Choose between `AUTH` or `SALE`.

-  **Accept Card Payments Via**:

-  `PayOrc Embedded Solution`

-  `PayOrc Hosted Solution`

-  **Payment Capture**:

-  `MANUAL`

-  `AUTOMATIC`

4. Click **Save** to apply the settings.

  

## Usage

Once configured, PayOrc will be available as a payment option at checkout. Depending on your settings, payments will be processed via either the **Embedded** or **Hosted** solution.



## Post Transaction

1. Navigate to **Extensions > Extensions** and select **Payment**.

2. Find **PayOrc** in the list and click **Edit**.
3. Latest transaction are available in the tabular form

  

## Support

For issues or questions, please contact PayOrc support or visit the official documentation.

  

---

  

### Security Notice

- Keep your **Merchant Secret** confidential.
