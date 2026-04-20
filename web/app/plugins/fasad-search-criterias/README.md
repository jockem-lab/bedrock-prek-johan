# Prek Web Helper

### Helper functions for prek

#### Helpers::isPrekUser()
append get param for testing non prek user: ?usertype=customer

### Cookies
#### Addon for cookies-and-content-security-policy.

It tries to create a file for cookies-and-content-security-policy-vars.php in /plugins with wp-load-path 
(an error message will be shown if this doesn't work)

There will be a new tab under Settings -> Cookies and content security policy named Prek, it will show
a couple of textareas with filters. 
The addon expects at least a filter for domain (prek_cacsp_settings_domains), copy to theme and adjust as needed.
When this filter is added the domain tabs textareas will be readonly and the values will be injected from the theme filter instead.

The addon will try to set some default values for cacsp, like policy page and some settings. If this fails, there are some optional filters for this too.

To reset saved cookie, append `?cacsp_reset=true` to url. Useful when customer needs to remove cookie.