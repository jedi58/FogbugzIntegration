# FogbugzIntegration
A PHP class for interacting with the FogBugz API. FogBugz is issue tracking software developed by Fogcreek (Joel Spolsky). In their own words:
> Used by over 20,000 software development teams for issue and bug tracking, project planning and management, collaboration and time tracking. All in one place.

The idea behind this class is that you can use this to pull out information to be used on your own website(s), or have your own web applications interact with it.

## Connecting to FogBugz
All requests to the FogBugz API must be authenticated. Unfortunately they do this using a plaintext password (so will appear in the server logs on the remote server - something to bear in mind if you host this yourself).
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
```

This will then give you a new FogbugzIntegration object which can be used to make API calls to retrieve or set data.


## Creating a new issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$issue_id = $fb->openTicket('My new issue', 'It does not work - please fix');
```

The `openTicket` function takes as arguments the title of the issue, a description about it, then an optional array for setting additional properties such as project, area, priority, etc.


## Updating an existing issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->updateTicket(1, 'An update to my ticket');
```

As with `openTicket`, the `updateTicket` function will take an optional array as it's last parameter for setting other case properties. The only mandatory parameters are for specifying the ticket ID, and the updated message to apply to it. If successful it will return the ID of the ticket.


## Reopening an issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->reopenTicket(1);
```

To reopen an issue pass in the ID of the ticket. This too will take an optional array as it's last parameter for setting other case properties. If successful it will return the ID of the ticket.


## Resolving an issue
This will mark a ticket as resolved, but will not close it.
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->resolveTicket(1);
```

If successful it will return the ID of the ticket.


## Closing an issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->closeTicket(1);
```

If successful it will return the ID of the ticket.
