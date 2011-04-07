#Description#
A simple library that allows you to easily access the Amazon Web Services APIs - at least the ones that are important to me. Not all methods for the APIs are supported yet, they get added as I need to call them.

#Motivation#
Why did I create it? Because all the other libraries I found sucked, even the official AWS PHP SDK. Most of the libraries I tested generated signatures differently and none of them properly URL Encoded all my data. That means that things would work fine... most of the time. Randomly having 2% of my results not make it into SimpleDB because they contained a special character wasn't acceptable to me and most of the libraries I looked at were so horribly written that it was impossible to track down the root cause - so I started fresh.

#Usage#
Just include the base ``aws.php`` file and it will register its autoloader. From there on out you just need to create a new instance of the API you want:

	$sdb = new SimpleDB().

##Configuration##
The easiest way to specify your API credentials is to define the two constants somewhere in your code before you instantiate an API object:

	define( 'AWS_KEY', '' );
	define( 'AWS_SECRET_KEY', '' );

You can also pass them to the constructor for your API:

	$sdb = new SimpleDB( 'key', 'secret' );

Passing values to the constructor will take precedence over constants, if they're defined.