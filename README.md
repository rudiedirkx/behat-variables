Behat Variables
====

[![Build Status](https://travis-ci.org/rudiedirkx/behat-variables.svg?branch=master)](https://travis-ci.org/rudiedirkx/behat-variables)

Stores your custom variables across Scenarios during Feature testing.

The idea
----

You're making users and profiles to test, and you want to use those creations further in
the same Feature. You can't, because: 1) Behat arguments are literals, and 2) FeatureContext
lives only for every Scenario, not for the entire Feature.

With behat-variables, you can save & use those variables: user ids, passwords, activation
tokens, etc.

	Given a new user "Fred"           # Your custom step, with a return value
	And I save it into "UID"          # A provided step that saves that return value
	When I go to "/users/<<UID>>"     # Custom step, with dynamic argument
	Then I should see "Hello, Fred!"  # Custom step, with predictable content

The `<<UID>>` part is the magic. Works for any argument, not just URIs.

Set up
----

In order to use variables in your Behat feature, you must do 2 things:

* Add the Feature Context class: `rdx\behatvars\BehatVariablesContext`
* Add the Extension class: `rdx\behatvars\BehatVariablesExtension`

See the Behat docs for where they fit in `behat.yml`, or see this repo's `behat.yml`:

	default:
	  suites:
	    default:
	      contexts:
	        - rdx\behatvars\BehatVariablesContext
	        - FeatureContext
	  extensions:
	      rdx\behatvars\BehatVariablesExtension: ~

Examples
----

See `features/simple.feature` for very simple examples (with only 3 custom steps). It's the
test used to test this package.

Features
----

This package provides 1 step, in several formats:

	(I|we) save (it|that|those|them) into "VARIABLE_NAME"

So you can make several custom step combinations:

	Given "4" cars in the same shop                   # Custom
	And we save those into "CAR1,CAR2,CAR3,CAR4"      # Provided

	Given a user "Fred" in organization "McDonald's"  # Custom
	And we save those into "USER,ORGANIZATION"        # Provided

Just make sure your custom steps have a **scalar return value, or an array of scalars**.
