Feature: Load package classes into feature

	Scenario: Remember 1 variable
		Given a value "50"
		When I save it into "A"
		Then "<<A>>" should equal "50"

	Scenario: Remember 1 variable twice
		Given a value "50"
		And I save it into "B"
		And a value "foo bar"
		And I save it into "C"
		Then "<<B>>" should equal "50"
		And "<<C>>" should equal "foo bar"

	Scenario: Remember 2 variables
		Given values "50" and "foo bar"
		When I save them into "D,E"
		Then "<<D>>" should equal "50"
		And "<<E>>" should equal "foo bar"

	Scenario: Remember 1 variable across scenarios
		Then "<<A>>" should equal "50"

	Scenario: Remember 2 variables from different scenarios, across scenarios
		Given a value "100"
		And I save it into "A"
		Then "<<A>>" should equal "100"
		And "<<B>>" should equal "50"

	Scenario: The database
		Then the database should contain:
		"""
		| A | 100     |
		| B | 50      |
		| C | foo bar |
		| D | 50      |
		| E | foo bar |
		"""
