# Requirements Document

## Introduction

This feature implements a penalty fee configuration system for JJ Calçados that allows administrators to configure penalty rates, interest rates, collection periods, and grace periods for overdue payments. The system will provide a centralized configuration interface accessible from the main dashboard alongside the existing discount configuration.

## Requirements

### Requirement 1

**User Story:** As an administrator, I want to configure penalty fee rates and interest rates, so that I can manage overdue payment charges according to business policies.

#### Acceptance Criteria

1. WHEN an administrator accesses the penalty configuration THEN the system SHALL display current penalty rate and interest rate settings
2. WHEN an administrator updates penalty rates THEN the system SHALL validate that rates are between 0% and 100%
3. WHEN an administrator saves penalty configuration THEN the system SHALL store the new rates in the database
4. IF penalty rates are invalid THEN the system SHALL display validation error messages

### Requirement 2

**User Story:** As an administrator, I want to configure collection days and grace period days, so that I can control when penalties start applying and collection processes begin.

#### Acceptance Criteria

1. WHEN an administrator sets collection days THEN the system SHALL accept values between 1 and 365 days
2. WHEN an administrator sets grace period days THEN the system SHALL accept values between 0 and 90 days
3. WHEN grace period is set THEN the system SHALL ensure it is less than or equal to collection days
4. IF day values are invalid THEN the system SHALL display appropriate validation messages

### Requirement 3

**User Story:** As an administrator, I want to access penalty configuration from the main dashboard, so that I can quickly manage penalty settings alongside other configurations.

#### Acceptance Criteria

1. WHEN an administrator views the main dashboard THEN the system SHALL display a penalty configuration card next to the discount configuration card
2. WHEN an administrator clicks the penalty configuration card THEN the system SHALL navigate to the penalty configuration page
3. WHEN the penalty configuration card is displayed THEN the system SHALL show current penalty rate and collection days as summary information

### Requirement 4

**User Story:** As an administrator, I want the penalty configuration to be persistent and secure, so that settings are maintained across sessions and only authorized users can modify them.

#### Acceptance Criteria

1. WHEN penalty configuration is saved THEN the system SHALL persist settings in the database
2. WHEN the application restarts THEN the system SHALL load previously saved penalty configuration
3. WHEN a non-administrator user attempts to access penalty configuration THEN the system SHALL deny access and redirect appropriately
4. WHEN penalty configuration is modified THEN the system SHALL log the change with user and timestamp information

### Requirement 5

**User Story:** As an administrator, I want to see validation feedback and confirmation messages, so that I know when penalty configuration changes are successful or if there are errors.

#### Acceptance Criteria

1. WHEN penalty configuration is successfully saved THEN the system SHALL display a success message using SweetAlert2
2. WHEN validation errors occur THEN the system SHALL display specific error messages for each invalid field
3. WHEN required fields are empty THEN the system SHALL prevent form submission and highlight missing fields
4. WHEN configuration is loaded THEN the system SHALL display current values in the form fields