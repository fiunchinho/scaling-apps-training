# Load Balancing
## Layers in the OSI and Internet Models
The notion of seven networking layers comes from the [Open Systems Interconnection (OSI) Reference Model](https://en.wikipedia.org/wiki/OSI_model). The model separates network functions into seven abstracted layers, commonly referred to by their numbers (Layer 1 through Layer 7). At each layer there are standards that define how data is packaged and transported. Among other things, the standards define how to segment the stream of bits that constitute a request or response into discrete packages called protocol data units (PDUs). The standards also define the metadata added to each PDU in the form of a header; the metadata might specify the addresses of the origin and destination hosts, for example.

Assigning different aspects of network functionality to different layers simplifies the processing at each layer, because a protocol only has to know how to deal with its own layer’s PDUs, and what metadata to include in the header so that the protocols at the adjacent layers can repackage the PDUs at their own level of data segmentation.

The distribution of network functions among the basic protocols for traffic on the World Wide Web – which are collectively referred to as the [Internet protocol (IP)](https://en.wikipedia.org/wiki/Internet_Protocol) suite – does not conform exactly to the OSI model. This is because the IP suite was defined and implemented before the finalized OSI model was published in 1984. Nonetheless, the various protocols in the IP suite do perform distinct functions that roughly correspond to OSI layers.

There are multiple protocols defined at each level, but the following are the protocols and levels relevant to load balancing of website traffic:

- Internet Protocol (IP) operates at the internetwork layer (Layer 3). Its PDUs are called packets, and IP is responsible for delivering them from a origin host to a destination host, usually across the boundaries between the multiple smaller networks that make up the Internet. Each device that is directly connected to the Internet has a unique IP address, which is used to locate the device as the recipient of packets.
- Transmission Control Protocol (TCP) operates at the transport layer (Layer 4). TCP effectively creates a virtual connection between the host where the browser is running and the host where a server application is running. Because of the unreliable nature of networks, IP packets can be lost, corrupted, or arrive out of order. TCP has mechanisms for correcting these errors, transforming the stream of IP packets into a reliable communication channel. Each application is assigned a unique TCP port number to enable delivery to the correct application on hosts where many applications are running.
- Hypertext Transfer Protocol (HTTP) operates at the application layer (Layer 7). It defines how data is encoded for communication between web browsers and web servers (or any application that understands HTTP encoding).

## DNS Load Balancing
DNS load balancing relies on the fact that most clients use the first IP address they receive for a domain. In most Linux distributions, DNS by default sends the list of IP addresses in a different order each time it responds to a new client, using the round-robin method. As a result, different clients direct their requests to different servers, effectively distributing the load across the server group.

Unfortunately, this simple implementation of DNS load balancing has inherent problems that limit its reliability and efficiency. Most significantly, DNS does not check for server or network outages or errors, and so always returns the same set of IP addresses for a domain even if servers are down or inaccessible.

Another issue arises because resolved addresses are usually cached, by both intermediate DNS servers (called resolvers) and clients, to improve performance and reduce the amount of DNS traffic on the network. Each resolved address is assigned a validity lifetime (called its time-to-live, or TTL), but long lifetimes mean that clients might not learn about changes to the group of servers in a timely fashion, and short lifetimes improve accuracy but lead to the increased processing and DNS traffic that caching is meant to mitigate in the first place.
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

However, persistence and affinity create new problems. The load balancer now has more responsibilities, which can impact its performance and turn it into a single point of failure. This approach can also create cold and hot spots within the cluster; returning users will always access the same server, even when new nodes are added to the cluster.

## Health Check
Load balancers allow us to set up health checks to determine if a backend server is available to process requests. This avoids having to manually remove a server from the backend if it becomes unavailable.
Once defined, the load balancer periodically checks if the backend server is responding to the health check successfully, i.e. it checks if the backend is listening on the configured IP address and port.

If a server fails a health check, and therefore is unable to serve requests, it is automatically disabled in the backend i.e. traffic will not be forwarded to it until it becomes healthy again. If all servers in a backend fail, the service will become unavailable until at least one of those backend servers becomes healthy again.

## Common Load Balancers
- [HAProxy](http://haproxy.1wt.eu/)
- [Nginx](http://nginx.org/)
- [AWS Elastic Load Balancer](http://aws.amazon.com/elasticloadbalancing/)

## Sources
- [What is DNS Load Balancing?](https://www.nginx.com/resources/glossary/dns-load-balancing/)
- [What is Layer 4 Load Balancing?](https://www.nginx.com/resources/glossary/layer-4-load-balancing/)
- [What is Layer 7 Load Balancing?](https://www.nginx.com/resources/glossary/layer-7-load-balancing/)
- [HA Proxy Load Balancing FAQ](http://blog.haproxy.com/loadbalancing-faq/)
- [An Introduction to HAProxy and Load Balancing Concepts](https://www.digitalocean.com/community/tutorials/an-introduction-to-haproxy-and-load-balancing-concepts)

## Deployment of this repository
If you want to deploy the content of this repository to an EC2 instance follow these simple steps.
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