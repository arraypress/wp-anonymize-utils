# WordPress Anonymize Utilities

A lightweight WordPress library for anonymizing sensitive data types including emails, personal information, IP addresses, and financial data. Perfect for GDPR compliance, privacy protection, and maintaining database integrity during anonymization.

## Features

* 🔒 **GDPR Compliant**: Placeholder emails maintain database integrity while ensuring compliance
* 📧 **Email Anonymization**: Handles complex domains (.co.uk, .museum, subdomains)
* 🌐 **IP Support**: Full IPv4 and IPv6 anonymization with network preservation
* 👤 **Personal Data**: Names, phones, addresses, dates with structure preservation
* 💳 **Financial Data**: Credit cards, bank accounts, tax IDs for e-commerce sites
* 🔗 **WordPress Integration**: User data, comments, and meta fields
* 🌍 **Unicode Support**: International names, addresses, and characters
* ⚡ **Lean & Fast**: Only essential methods, no bloat
* ✅ **Validation**: Centralized anonymization detection

## Requirements

* PHP 7.4 or later
* WordPress 5.0 or later

## Installation

```bash
composer require arraypress/wp-anonymize-utils
```

## Basic Usage

### Email Anonymization

```php
use ArrayPress\AnonymizeUtils\Email;

// Basic anonymization
$anonymized = Email::anonymize( 'john.doe@example.com' );
// Returns: "jo*****@ex*****.com"

// Works with complex domains
$complex = Email::anonymize( 'user@subdomain.example.co.uk' );
// Returns: "us***@su*******.example.co.uk"

// Mask for display (preserve domain)
$masked = Email::mask( 'john@example.com', 1, 1 );
// Returns: "j**n@example.com"

// GDPR-compliant placeholder
$placeholder = Email::placeholder( 'user@example.com' );
// Returns: "deleted@site.invalid"

// Check if already anonymized
if ( Email::is_anonymized( 'jo***@ex*****.com' ) ) {
	// Already anonymized
}

// Anonymize multiple emails
$emails     = [ 'user1@example.com', 'user2@test.org' ];
$anonymized = Email::anonymize_multiple( $emails );
// Returns: ["us***@ex*****.com", "us***@te**.org"]
```

### Personal Data Anonymization

```php
use ArrayPress\AnonymizeUtils\Personal;

// Anonymize names (preserves structure)
$name = Personal::name( 'John Smith' );
// Returns: "J**n S***h"

// Works with international names
$international = Personal::name( 'José María' );
// Returns: "J**é M***a"

// Anonymize phone numbers
$phone = Personal::phone( '555-123-4567' );
// Returns: "******4567"

// International phone formats
$intl_phone = Personal::phone( '+44 20 7946 0958' );
// Returns: "*******0958"

// Anonymize addresses
$address = Personal::address( '123 Main Street' );
// Returns: "*** **** ******"

// Multi-line addresses
$full_address = Personal::address( "123 Main St\nApt 4B" );
// Returns: "*** **** **\n*** **"

// Anonymize dates (preserve month/year)
$date = Personal::date( '1990-05-15' );
// Returns: "1990-05-**"

// Anonymize postal codes
$zip = Personal::zipcode( '90210' );
// Returns: "**210"

// Anonymize any text
$text = Personal::text( 'Sensitive information' );
// Returns: "********* ***********"

// Bulk anonymization
$data       = [
	'name'    => 'John Smith',
	'phone'   => '555-1234',
	'address' => '123 Main St'
];
$anonymized = Personal::anonymize_multiple( $data );
// Returns: ['name' => 'J**n S***h', 'phone' => '****1234', ...]
```

### IP Address Anonymization

```php
use ArrayPress\AnonymizeUtils\IP;

// IPv4 anonymization
$ipv4 = IP::anonymize( '192.168.1.100' );
// Returns: "192.168.1.0"

// IPv6 anonymization
$ipv6 = IP::anonymize( '2001:db8:85a3::8a2e:370:7334' );
// Returns: "2001:db8:85a3::8a2e:370:0"

// IPv6 localhost
$localhost = IP::anonymize( '::1' );
// Returns: "::0"

// Mask instead of zero
$masked_ip = IP::mask_last_octet( '192.168.1.100' );
// Returns: "192.168.1.***"

// Check if anonymized
if ( IP::is_anonymized( '192.168.1.0' ) ) {
	// Already anonymized
}

// Get current user's anonymized IP
$user_ip = IP::get_user_anonymized();
// Returns: "192.168.1.0" (or null if unavailable)

// Anonymize multiple IPs
$ips        = [ '192.168.1.100', '10.0.0.50' ];
$anonymized = IP::anonymize_multiple( $ips );
// Returns: ["192.168.1.0", "10.0.0.0"]
```

### Financial Data Anonymization

```php
use ArrayPress\AnonymizeUtils\Financial;

// Credit card numbers
$card = Financial::credit_card( '4532-1234-5678-9012' );
// Returns: "************9012"

// Bank account numbers
$account = Financial::bank_account( '123456789012' );
// Returns: "********9012"

// Tax ID numbers
$tax_id = Financial::tax_id( '12-3456789' );
// Returns: "******6789"

// Custom keep length
$card_custom = Financial::credit_card( '4532123456789012', 6 );
// Returns: "**********789012"

// Check if anonymized
if ( Financial::is_anonymized( '************9012' ) ) {
	// Already anonymized
}

// Bulk financial anonymization
$financial_data = [
	'card'    => '4532123456789012',
	'account' => '123456789',
	'tax_id'  => '12-3456789'
];
$anonymized     = Financial::anonymize_multiple( $financial_data );
// Auto-detects types based on field names
```

### Web Data Anonymization

```php
use ArrayPress\AnonymizeUtils\Web;

// Anonymize URLs (preserve domain)
$url = Web::url( 'https://example.com/user/profile?id=123' );
// Returns: "https://example.com/****/*******/***"

// User agent simplification
$ua = Web::user_agent( 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) Chrome/91.0' );
// Returns: "Chrome on Windows"

// Check if URL is anonymized
if ( Web::is_url_anonymized( 'https://example.com/****/profile' ) ) {
	// Already anonymized
}

// Check if user agent is anonymized
if ( Web::is_user_agent_anonymized( 'Chrome on Windows' ) ) {
	// Already anonymized
}

// Bulk web data anonymization
$web_data   = [
	'url'        => 'https://example.com/user/123',
	'user_agent' => 'Mozilla/5.0...',
	'referrer'   => 'https://google.com/search?q=example'
];
$anonymized = Web::anonymize_multiple( $web_data );
```

## WordPress Integration

### User Data Anonymization

```php
use ArrayPress\AnonymizeUtils\User;

// Anonymize WordPress user
$success = User::anonymize_data( 123 );
// Anonymizes: email, name, nicename, url, display_name

// Anonymize specific fields only
$success = User::anonymize_data( 123, [ 'user_email', 'first_name' ] );

// Anonymize user meta data
$success = User::anonymize_meta( 123 );
// Anonymizes: billing info, shipping info, etc.

// Anonymize specific meta keys
$success = User::anonymize_meta( 123, [ 'billing_email', 'billing_phone' ] );

// Check if user is anonymized
if ( User::is_anonymized( 123 ) ) {
	// User is already anonymized
}

// Bulk anonymize users
$user_ids = [ 123, 456, 789 ];
$results  = User::bulk_anonymize( $user_ids );
// Returns: [123 => true, 456 => true, 789 => false]

// Get anonymized export data
$export_data = User::get_anonymized_export( 123 );
```

### Comment Data Anonymization

```php
use ArrayPress\AnonymizeUtils\Comment;

// Anonymize WordPress comment
$success = Comment::anonymize_data( 456 );
// Anonymizes: author name, email, URL, IP address

// Check if comment is anonymized
if ( Comment::is_anonymized( 456 ) ) {
	// Comment is already anonymized
}

// Bulk anonymize comments
$comment_ids = [ 123, 456, 789 ];
$results     = Comment::bulk_anonymize( $comment_ids );

// Anonymize all comments for a post
$results = Comment::anonymize_by_post( 789 );

// Get anonymized export data
$export_data = Comment::get_anonymized_export( 456 );
```

## Validation and Detection

### Universal Validation

```php
use ArrayPress\AnonymizeUtils\Validate;

// Check if any data is anonymized
if ( Validate::is_anonymized( $some_data ) ) {
	// Data is anonymized
}

// Works with any data type
Validate::is_anonymized( 'jo***@example.com' );    // true
Validate::is_anonymized( '192.168.1.0' );          // true  
Validate::is_anonymized( 'J**n S***h' );           // true
Validate::is_anonymized( '************1234' );     // true
Validate::is_anonymized( 'Chrome on Windows' );    // true

// WordPress-specific validation
if ( Validate::is_user_anonymized( 123 ) ) {
	// WordPress user is anonymized
}

if ( Validate::is_comment_anonymized( 456 ) ) {
	// WordPress comment is anonymized
}
```

### Convenience Methods

```php
// Each class has its own is_anonymized() method for convenience
Email::is_anonymized( $email );
IP::is_anonymized( $ip );
Financial::is_anonymized( $card_number );
User::is_anonymized( $user_id );
Comment::is_anonymized( $comment_id );

// All delegate to the centralized Validate class
```

## Advanced Examples

### GDPR Data Export Anonymization

```php
// Anonymize exported user data
$user_data = get_userdata( 123 );

$anonymized_export = [
	'email'   => Email::placeholder( $user_data->user_email ),
	'name'    => Personal::name( $user_data->display_name ),
	'ip'      => IP::anonymize( $user_data->last_login_ip ),
	'address' => Personal::address( $user_data->billing_address )
];
```

### Contact Form Anonymization

```php
// Anonymize contact form submissions
function anonymize_contact_form( $submission_data ) {
	return [
		'name'    => Personal::name( $submission_data['name'] ),
		'email'   => Email::placeholder( $submission_data['email'] ),
		'phone'   => Personal::phone( $submission_data['phone'] ),
		'message' => Personal::text( $submission_data['message'] ),
		'ip'      => IP::anonymize( $submission_data['ip'] )
	];
}
```

### Analytics Data Anonymization

```php
// Anonymize analytics while preserving structure
function anonymize_analytics( $analytics_data ) {
	return [
		'user_ip'    => IP::anonymize( $analytics_data['ip'] ),
		'user_agent' => Web::user_agent( $analytics_data['user_agent'] ),
		'referrer'   => Web::url( $analytics_data['referrer'] ),
		'page_url'   => Web::url( $analytics_data['page_url'] )
	];
}
```

### Bulk Data Processing

```php
// Process large datasets with validation
function process_user_data( $user_ids ) {
	$results = [];
	
	foreach ( $user_ids as $user_id ) {
		// Skip if already anonymized
		if ( User::is_anonymized( $user_id ) ) {
			$results[ $user_id ] = 'already_anonymized';
			continue;
		}
		
		// Anonymize user data and meta
		$success = User::anonymize_data( $user_id ) && 
		           User::anonymize_meta( $user_id );
		           
		$results[ $user_id ] = $success ? 'success' : 'failed';
	}
	
	return $results;
}
```

## Key Features

- **GDPR Compliant**: Maintains database integrity with placeholder values
- **Complex Domain Support**: Handles .co.uk, .museum, subdomains perfectly
- **Full IP Support**: IPv4, IPv6, all formats with network preservation
- **Unicode Ready**: International names, addresses, characters
- **WordPress Integration**: Users, comments, meta data
- **Financial Data**: Credit cards, bank accounts for e-commerce
- **Flexible Methods**: Multiple anonymization approaches per data type
- **Centralized Validation**: Universal anonymization detection
- **Lean Codebase**: Only essential functionality, no bloat

## Best Practices

1. **Check before anonymizing**:
```php
if ( ! Email::is_anonymized( $email ) ) {
    $email = Email::anonymize( $email );
}
```

2. **Use placeholders for GDPR compliance**:
```php
// Good: Maintains referential integrity
Email::placeholder( $email );

// Avoid: Breaks database relationships
// delete_user_meta( $user_id, 'email' );
```

3. **Use appropriate anonymization level**:
 ```php
 // For display: partial masking
 Email::mask( $email, 2, 2 );
 
 // For storage: full anonymization
 Email::anonymize( $email );
 
 // For deletion: placeholder
 Email::placeholder( $email );
 ```

4. **Leverage centralized validation**:
```php
// Universal validation works for any data type
if ( Validate::is_anonymized( $data ) ) {
    // Already processed
}
```

## Requirements

- PHP 7.4+
- WordPress 5.0+

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## License

This project is licensed under the GPL-2.0-or-later License.

## Support

- [Documentation](https://github.com/arraypress/wp-anonymize-utils)
- [Issue Tracker](https://github.com/arraypress/wp-anonymize-utils/issues)