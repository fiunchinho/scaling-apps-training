# Load Balancing

## Layer 4 Load Balancing
The simplest way to load balance network traffic to multiple servers is to use layer 4 load balancing. Layer 4 is related to the fourth layer of the OSI model: **transport level** (for example: TCP and UDP protocols are transport level). It is really fast but can’t perform any action on the protocol above layer 4.

Load balancing this way will forward user traffic based on IP range and port. It has a packet view of the traffic exchanged between the client and a server which means it takes decisions packet by packet.
For example: if a request comes in for http://yourdomain.com/anything, the traffic will be forwarded to the backend that handles all the requests for yourdomain.com on port 80.

## Layer 7 Load Balancing
Another, more complex way to load balance network traffic is to use layer 7 load balancing. Layer 7 is related to the seventh layer of the OSI model: **application level** (for example. HTTP, FTP, SMTP, DNS protocols are application level).
The packets are re-assembled (we also say that it *terminates* the network traffic) then the load-balancer can forward requests to different backend servers based on the content of the client's request.

The layer 7 load-balancer acts as a proxy, which means it maintains two TCP connections: one with the client and one with the server. It's slower than Level 4 Load Balancing.

## Load Balancing Algorithms
The load balancing algorithm that is used determines which server, in a backend, will be selected when load balancing. A few of the most commonly used algorithms are as follows:

- **Random**: Backend server is chosen randomly.

- **Round Robin**: Round Robin selects servers in turns. This is the most common and simple algorithm.

- **Weighted Round Robin**: As Round Robin, but some servers get a larger share of the overall traffic.

- **Least connections / weighted least connections**: Selects the server with the least number of connections--it is recommended for long lived connections.

- **Least traffic / weighted least traffic:** The load balancer monitors the bitrate from each server, and sends to the server that has the least outgoing traffic.

- **Least latency:** Load balanced periodically send a small request to backend servers to calculate the latency, and sends the request to the fastest server to respond.

- **Source IP hash:** Connections are distributed to backend servers based on a hash of the source IP address. If a backend server fails and is marked as unhealthy/down, the distribution changes. As long as all servers are running a given client IP address will always go to the same web server.

- **URL hash:** Much like source IP hash, except hashing is done on the URL of the request. Useful when load balancing in front of proxy caches, as requests for a given object will always go to just one backend cache. This avoids cache duplication, having the same object stored in several / all caches, and increases effective capacity of the backend caches.

## Persistence and Affinity
Sometimes some state is saved on the backend server (like the user session data), so instead of using one of the algorithms explained above, we need to send all requests in a user session, consistently to the same backend server. When we need this behaviour, we say that we need **Sticky Sessions**. There two different strategies to achieve this:
- **Affinity** is when we use information from a layer **below the application layer** to maintain a client request consistently going to a single server. For example, using the client IP address.
- **Persistence** is when we use **application layer information** to stick a client to a single server. For example, creating a cookie that is used to map the session to the backend server.

The main advantage of the persistence over affinity is that it’s much more accurate, but sometimes, persistence is not doable, so we must rely on affinity.

Using persistence, we’re 100% sure that a user will get redirected to a single server.
Using affinity, the user **may be redirected** to the same server.

## Health Check
Load balancers allow us to set up health checks to determine if a backend server is available to process requests. This avoids having to manually remove a server from the backend if it becomes unavailable.
Once defined, the load balancer periodically checks if the backend server is responding to the health check successfully, i.e. it checks if the backend is listening on the configured IP address and port.

If a server fails a health check, and therefore is unable to serve requests, it is automatically disabled in the backend i.e. traffic will not be forwarded to it until it becomes healthy again. If all servers in a backend fail, the service will become unavailable until at least one of those backend servers becomes healthy again.

## Common Load Balancers
- [HAProxy](http://haproxy.1wt.eu/)
- [Nginx](http://nginx.org/)
- [AWS Elastic Load Balancer](http://aws.amazon.com/elasticloadbalancing/)

## Deployment of this repository
If you want to deploy the content of this repository to an EC2 instance to test the caching mechanisms, like CloudFront, follow these simple steps.
Go to the AWS Console and start a new EC2 micro instance. Choose the default VPC. To be able to later access the machine, create a new pair of SSH keys or choose an already existing pair. Wait for it to be available.

While this happens, install [Ansible](https://docs.ansible.com/ansible/) and [Ansistrano](https://github.com/ansistrano/deploy) on your Vagrant

```bash
$ sudo easy_install pip
$ sudo pip install ansible
$ ansible-galaxy install carlosbuenosvinos.ansistrano-deploy carlosbuenosvinos.ansistrano-rollback
```

The EC2 instance should be available now. Go to the AWS Console and then to Security Groups. Choose the security group that you are using for the instance and allow all traffic from the internet to the machine. This is insecure but it's just for this training.
Try sshing into the machine selecting your pem key. Choose the key that you selected while creating the instance. Remember that for Ubuntu instances, the SSH user is `ubuntu`.

```bash
$ ssh -i $HOME/.ssh/personal.pem ubuntu@52.123.456.78
```

Edit the `deploy.yml` and `rollback` files, mainly updating the `ansistrano_deploy_to` variable. This is the path where your application will be deployed.
There is an `infrastructure.yml` that makes sure that your server has php and apache installed, which is executed every time we deploy.

Finally, execute Ansible to deploy

```bash
$ ansible-playbook --private-key $HOME/.ssh/personal.pem -u ubuntu -i 52.48.238.88, deploy.yml
```

Try to deploy several times and watch the `releases` folder grow.

## Exercise
Let's deploy an example application in two different AWS EC2 instances, with an Elastic Load Balancer in front of them so traffic is balanced between both instances. This example application just prints the hostname and the IP of the server, so you can see which one is responding the request.

Start two instances and wait for them to be available. Use the instructions above to deploy the code in this repository. Check if you can reach the index.php in your browser.

Then create a Elastic Load Balancer and choose the instances that you just started. Wait until the health check mark the instances as `InService`, and then load the load balancer DNS in your browser. You should see a different hostname and IP every time.