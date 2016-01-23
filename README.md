# FogbugzIntegration
A PHP class for interacting with the FogBugz API. FogBugz is issue tracking software developed by Fogcreek (Joel Spolsky). In their own words:
> Used by over 20,000 software development teams for issue and bug tracking, project planning and management, collaboration and time tracking. All in one place.

The idea behind this class is that you can use this to pull out information to be used on your own website(s), or have your own web applications interact with it.


## Usage
Below are examples for using this class.

1. [Connecting to FogBugz](#connecting)
2. [Closing the API connection](#logoff)
3. [Creating a new issue](#create)
4. [Updating an existing issue](#update)
5. [Reopening an issue](#reopen)
6. [Resolving an issue](#resolve)
7. [Closing an issue](#close)
8. [Retrieving an issue](#get)
9. [Searching for an issue](#search)
10. [Using filters](#filter)
11. [Retrieving a list of projects](#getProjects)
12. [Retrieving a list of available priorities](getPriorities)

<a name="connecting"></a>
### Connecting to FogBugz
All requests to the FogBugz API must be authenticated. Unfortunately they do this using a plaintext password (so will appear in the server logs on the remote server - something to bear in mind if you host this yourself).
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
```

This will then give you a new FogbugzIntegration object which can be used to make API calls to retrieve or set data.


<a name="logoff"></a>
### Closing the API connection
```php
$fb->logout();
```


<a name="create"></a>
### Creating a new issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$issue_id = $fb->openTicket('My new issue', 'It does not work - please fix');
```

The `openTicket` function takes as arguments the title of the issue, a description about it, then an optional array for setting additional properties such as project, area, priority, etc.


<a name="update"></a>
### Updating an existing issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->updateTicket(1, 'An update to my ticket');
```

As with `openTicket`, the `updateTicket` function will take an optional array as it's last parameter for setting other case properties. The only mandatory parameters are for specifying the ticket ID, and the updated message to apply to it. If successful it will return the ID of the ticket.


<a name="reopen"></a>
### Reopening an issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->reopenTicket(1);
```

To reopen an issue pass in the ID of the ticket. This too will take an optional array as it's last parameter for setting other case properties. If successful it will return the ID of the ticket.


<a name="resolve"></a>
### Resolving an issue
This will mark a ticket as resolved, but will not close it.
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->resolveTicket(1);
```

If successful it will return the ID of the ticket.


<a name="close"></a>
### Closing an issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$fb->closeTicket(1);
```

If successful it will return the ID of the ticket.


<a name="get"></a>
### Retrieving an issue
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$issue = $fb->getTicket(1);
```

Returns a SimpleXML object containing all details for the issue


<a name="search"></a>
### Searching for an issue
It is also possible to search for cases by a keyword.
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$issue = $fb->search('example');
```

This returns a SimpleXML object containing all details for the issue


<a name="filter"></a>
### Using filters
Filters defined in FogBugz can also be utilised if they are available to the user who is signed in to the API.
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$issue = $fb->setFilter(1);
```


<a name="getProjects"></a>
### Retrieving a list of projects
The result of this is limited to what the user signed into the API has access to.
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$projects = $fb->getProjectList();
```

This will retrieve an array where each element is an array of the project name and owner, and is indexed by the ID of the project. Passing in `true` to this function will return the raw SimpleXML output from the API instead.


<a name="getPriorities"></a>
### Retrieving a list of available priorities
The result of this is limited to what the user signed into the API has access to.
```php
$fb = new FogbugzIntegration('http://example.fogbugz.com', 'user@example.com', 'password');
$priorities = $fb->getAllPriorities();
```

This will retrieve an array where each element is an array of the priority name and a flag indicating if it's the default, and is indexed by the ID of the priority. Passing in `true` to this function will return the raw SimpleXML output from the API instead.
