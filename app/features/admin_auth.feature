Feature:
	In order to gain access to site management
	As an admin
	I need to be able to login and logout
	
	Scenario: Logging in as an admin
		Given there is an admin user "admin@ignis.ee" with password "admin"
		And I am on named route "home"
		When I follow "Login"
		And I fill in "Email" with "admin@ignis.ee"
		And I fill in "Password" with "admin"
		And I press "Login"
		Then I should see "Logout"
	
