# Horizontally Scaling Applications
In a nutshell, scalability is the ability of a system to handle an increased amount of traffic or processing and accommodate growth while maintaining a desirable user experience. There are basically two ways of scaling a system: vertically, also known as scaling up, and horizontally, also known as scaling out.

Vertical scaling (scaling up) is accomplished by increasing system resources, like adding more memory and processing power.

Horizontal scaling (scaling out), on the other hand, is accomplished by adding more servers to an existing cluster.

A cluster is simply a group of servers. A load balancer distributes the workload between the servers in a cluster. At any point, a new web server can be added to the existing cluster to handle more requests from users accessing your application; this is horizontal scaling. The load balancer has a single responsibility: deciding which server from the cluster will receive a request that was intercepted.

When user A makes a request to mydomain.com, the load balancer will forward requests to server1. User B, on the other hand, gets forwarded another node from the cluster, server2.

What happens when user A makes changes to the application, like uploading files or updating content in the database? How do you maintain consistency across all nodes in the cluster? Further, PHP saves session information in disk by default. If user A logs in, how can we keep that user’s session in subsequent requests, considering that the load balancer could send them to another server in the cluster?

Another issue when adding more servers is that the deployment of your code is not that easy anymore. It needs to be fast, otherwise different servers of your cluster would have different versions of your code, leading to unexpected behaviour.

## Exercise
Deploy the [Symfony Demo application](https://github.com/symfony/symfony-demo) in two EC2 instances, and configure an Elastic Load Balancer in front of them.
Since requests can reach either of the two servers, let's prepare everything so web servers can share information like database, cache and user sessions.

### Deployment
Use Ansistrano to deploy the symfony demo code to all the EC2 instances.

### Database
First, there needs to be a separation between web server and database. This way, we can have multiple application nodes sharing the same database server. It's a first step, and it will give the app a small performance improvement by reducing the load of the web server.
In order to do this, just install MySQL in a new server. So your web servers can access the database, you just need to change one bit of configuration. Open mysql configuration file `/etc/mysql/my.cnf` and look for the `bind-address` entry. There we specify which IP address others will use to talk to this database server. Write here the IP of the server running the database.

### Cache
Install Redis on the same server where MySQL is running. Use Redis to cache database results, so we can improve the performance of our application.

### Sessions
Configure user sessions to be saved on Redis. Use the same Redis that we use for caching to store user session data.

## Sources
- [Horizontally Scaling PHP Applications](https://www.digitalocean.com/company/blog/horizontally-scaling-php-applications/)
- [Scaling PHP Book](https://www.scalingphpbook.com/)
- [5 Common Server Setups for your Web Application](https://www.digitalocean.com/community/tutorials/5-common-server-setups-for-your-web-application)
- [PHP Performance and Scaling talks, curated by Erika Heidi](https://www.youtube.com/playlist?list=PLseEp7p6EwiaiJx-AZqXgvpJNJgXuNeBx)