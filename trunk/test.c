/* This is a hash up of mini_httpd and: http://beej.us/guide/bgnet/examples/server.c */
/* The idea is I can, well, skip to the end ;) */
/* gcc -lssl -lcrypto test.c -o test */

#include <stdio.h>
#include <stdlib.h>
#include <unistd.h>
#include <errno.h>
#include <string.h>
#include <sys/types.h>
#include <sys/socket.h>
#include <netinet/in.h>
#include <arpa/inet.h>
#include <sys/wait.h>
#include <signal.h>

#include <openssl/ssl.h>
#include <openssl/err.h>

#define MYPORT 443
#define BACKLOG 10

int main(void)
{
	int sockfd, new_fd;  // listen on sock_fd, new connection on new_fd
	struct sockaddr_in my_addr;	// my address information
	struct sockaddr_in their_addr; // connector's address information
	socklen_t sin_size;
	int yes=1; // wot dis den?

	static char* certfile = "server.pem";
	static char* cipher;
	static SSL_CTX* ssl_ctx;
	static SSL* ssl;
	static int conn_fd;

	char buf[100000];

	sockfd = socket(AF_INET, SOCK_STREAM, 0);
	setsockopt(sockfd, SOL_SOCKET, SO_REUSEADDR, &yes, sizeof(int));

	my_addr.sin_family = AF_INET;		 // host byte order
	my_addr.sin_port = htons(MYPORT);	 // short, network byte order
	my_addr.sin_addr.s_addr = INADDR_ANY; // automatically fill with my IP
	memset(my_addr.sin_zero, '\0', sizeof my_addr.sin_zero);

	bind(sockfd, (struct sockaddr *)&my_addr, sizeof my_addr);
	listen(sockfd, BACKLOG);

	SSL_load_error_strings();
	SSLeay_add_ssl_algorithms();
	ssl_ctx = SSL_CTX_new( SSLv23_server_method() );
	SSL_CTX_use_certificate_file(ssl_ctx, certfile, SSL_FILETYPE_PEM);
	SSL_CTX_use_PrivateKey_file(ssl_ctx, certfile, SSL_FILETYPE_PEM);
	SSL_CTX_check_private_key(ssl_ctx);
	//SSL_CTX_set_cipher_list(ssl_ctx, cipher);

	while(1)
	{
		sin_size = sizeof their_addr;

		if ((new_fd = accept(sockfd, (struct sockaddr *)&their_addr, &sin_size)) == -1) {
			perror("accept");
			continue;
		}

		ssl = SSL_new(ssl_ctx);
		SSL_set_fd(ssl, new_fd);
		SSL_accept(ssl);

		SSL_read(ssl, buf, sizeof(buf));

		//printf("Request:\n %s", buf);
		printf("server: got connection from %s\n",inet_ntoa(their_addr.sin_addr));

		//SSL_write(ssl, buf, sizeof(buf));
		SSL_write(ssl, "Hello, world!\n", sizeof("Hello, world!\n"));
		//send(new_fd, "Hello, world!\n", 14, 0);
		close(new_fd);
	}
}
