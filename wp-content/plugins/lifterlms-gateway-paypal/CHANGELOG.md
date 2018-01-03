CHANGELOG
=========

v1.1.2 - 2017-10-21
-------------------

+ Ensure that 'SuccessWithWarning' responses still initiate express checkouts. This fixes an issue with non-latin character sets preventing checkout initiation.


v1.1.1 - 2017-07-17
-------------------

+ Fix error handling on first-time payment reference transaction which fail
+ Add logging for first-time payment reference transaction which fail


v1.1.0 - 2017-07-05
-------------------

+ Adds support for payment gateway switching introduced by LifterLMS Core 3.10.0
+ Manual recurring payments are now possible for PayPal accounts which do not have Reference Transactions Enabled
+ This add-on is now completely translateable with included POT file


v1.0.2 - 2017-02-10
-------------------

+ Added button source tracking


v1.0.1 - 2016-10-20
-------------------

+ Fix digital goods item category. Prevents "Error 10004: You are not signed up to accept payment for digitally delivered goods."


v1.0.0 - 2016-10-11
-------------------

+ Initial public release
