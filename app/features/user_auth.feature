Feature:
	In order to gain access to site
	As a regular user
	I need to be able to login and logout
	
	Scenario: Logging in as a regular user
		Given there is a regular user "user@ignis.ee" with password "admin"
		And I am on named route "home"
		When I follow "Login"
		And I fill in "Email" with "user@ignis.ee"
		And I fill in "Password" with "admin"
		And I press "Login"
		Then I should see "Logout"