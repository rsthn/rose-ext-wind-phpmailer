# PHPMailer Adapter for Wind

This extension adds expression functions to [Wind](https://github.com/rsthn/rose-ext-wind) to send emails using PHPMailer.

> **NOTE:** The extension detects the presence of Wind, when not installed, this extension will simply not be loaded.

# Installation

```sh
composer require rsthn/rose-ext-wind-phpmailer
```

## Configuration Section: `Mail`


|Field|Type|Name|Description|
|----|----|-----------|-------|
|host|`string`|SMTP host name.|Required
|username|`string`|Username for the SMTP server.|Required
|password|`string`|Password for the SMTP server.|Required
|port|`int`|Port number to connect.|Default is port `587`.
|secure|`boolean`, `string`|SMTP secure connection mode.|Default is `true`.<br/>Use `explicit` if port is 587, `implicit` otherwise.<br/>Set to `false` to disable and to `true` to use automatic detection based on port number.
|from|`string`|Email address of the sender.|Optional
|fromName|`string`|Name of the sender.|Optional


## Expression Functions

### `mail::send` name:string value:string ...
### `phpmailer::send` name:string value:string ...

Accepts one or more name:value pairs, the name describes the field to set in the `mail` object of PHPMailer. Currently supported:

|Name|Type|Description|
|----|----|-----------|
|RCPT|`string`|Email address of the recipient.
|RCPT|`array`|Email addresses of the recipients.
|FROM|`string`|Email of the sender.
|FROM-NAME|`string`|Name of the sender.
|SUBJECT|`string`|Subject of the message.
|BODY|`string`|HTML contents of the message.
|ATTACHMENT|`string`|Adds the specified file (path) as an attachment.
|ATTACHMENT|`map { name, data }`|Adds an attachment from a given data string.
|ATTACHMENT|`map { name, path }`|Adds an attachment from a given path.
|ATTACHMENT|`array`|Adds one or more attachments (each of which can be any of the previous forms).

Example:

```lisp
(mail::send
	RCPT 'example@host.com'
	SUBJECT 'This is a test.'
	BODY '<b>Thanks for reading this email.</b>'
)
```
