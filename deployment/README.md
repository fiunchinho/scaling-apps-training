# Deployment
Once you start adding more web servers that run your application, you need to find a way to automate the deployment of your code in the servers. Manually updating dozens of servers is out of the question, since it's error prone and time consuming process. You certainly don't want different servers running different versions of your code.

There are many different strategies to efficiently deploy your code to the servers, but here we'll focus on a tool called [Ansistrano](https://github.com/ansistrano/deploy).

[Ansistrano](https://github.com/ansistrano/deploy) is an [Ansible](https://docs.ansible.com/ansible/) set of roles that let you automate the deployment of new versions of your code to your servers.

## Folder structure
Ansistrano creates an specific folder structure in your servers to keep the deployed code

```
-- /var/www/my-app.com
|-- current -> /var/www/my-app.com/releases/20100509150741
|-- releases
|   |-- 20100509150741
|   |-- 20100509145325
|-- shared
```

Every time you deploy, Ansistrano creates a new folder inside the `releases` folder. The name of this folder is the current date and time, so the `releases` folder becomes an ordered list of your latest deployments.
It also creates a `current` folder that is not a regular folder. It's in fact a symlink to one of the deployed versions inside the `releases` folder. This current folder will typically be the document root for your web server.

Every time you deploy a new version, the `current` symlink gets updated to the new deployed version. Rolling back to a previous version can be done just updating the symlink to a previous deployment on the `releases` folder.

The `shared` folder is used to store files that must be shared across different versions, like file uploads.

## Installing Ansistrano
First, install [Ansible](https://docs.ansible.com/ansible/) and [Ansistrano](https://github.com/ansistrano/deploy) on your Vagrant

```bash
$ sudo easy_install pip
$ sudo pip install ansible
$ ansible-galaxy install carlosbuenosvinos.ansistrano-deploy carlosbuenosvinos.ansistrano-rollback
```

## Setting up your playbooks
Ansible uses _playbooks_ that contain different tasks to be executed on the target servers. In this repository there are have two different playbooks: one for deploying new code; and another one to rollback to previous versions in case something goes wrong.

Edit the `deploy.yml` and `rollback.yml` files. There are [many different parameters](https://github.com/ansistrano/deploy#role-variables) that you can set in the playbook files like

- `ansistrano_deploy_to`: This is the path where your application will be deployed on the remote servers.
- `ansistrano_keep_releases`: The number of deploys that are kept on the server, in case we want to rollback to a previous version.

Ansistrano lets you _hook_ your own tasks to specific events so you can add behaviour, like making sure some packages are installed on the servers.
For example, here there is an `infrastructure.yml` that makes sure that your server has PHP and Apache installed. This is executed on the `ansistrano_before_setup_tasks_file` phase. You can hook more files containing tasks to different Ansistrano phases.

Deploying your code is as easy as executing your playbook with Ansible against the list of servers

```bash
$ ansible-playbook --private-key $HOME/.ssh/personal.pem -u ubuntu -i 52.48.238.88, deploy.yml
```

Try to deploy several times and watch the `releases` folder grow. Where is the current folder pointing to? What happens when you execute the `rollback.yml` playbook?