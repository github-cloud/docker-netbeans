# Use phusion/baseimage as base image. To make your builds reproducible, make
# sure you lock down to a specific version, not to `latest`!
# See https://github.com/phusion/baseimage-docker/blob/master/Changelog.md for
# a list of version numbers.
FROM phusion/baseimage:0.9.16

# ...put your own build instructions here...
RUN sed -ie 's#archive#jp.archive#g' /etc/apt/sources.list && \
    sed -ie 's#main$#main universe#' /etc/apt/sources.list && \
    apt-get update -qq && apt-get install -qqy software-properties-common && \
    add-apt-repository ppa:webupd8team/java -y && \
    apt-get update -qq && \
    echo oracle-java8-installer shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections && \
    apt-get install -y oracle-java8-installer libxext-dev libxrender-dev libxtst-dev

ADD state.xml /tmp/state.xml

RUN curl -L http://download.netbeans.org/netbeans/8.0.2/final/bundles/netbeans-8.0.2-php-linux.sh -o /tmp/netbeans.sh && \
    chmod +x /tmp/netbeans.sh && \
    echo 'Installing netbeans' && \
    /tmp/netbeans.sh --verbose --silent --state /tmp/state.xml && \
    rm -rf /tmp/*

ADD run /usr/local/bin/netbeans

#------------------------------------------------
# Install phpenv libraries
#------------------------------------------------
RUN apt-get install -y sudo ack-grep curl lftp jq ca-certificates \
    git-core make bison gcc cpp g++ subversion exuberant-ctags git-flow \
    libxml2-dev libssl-dev \
    libcurl4-gnutls-dev libjpeg-dev libpng12-dev libmcrypt-dev \
    libreadline-dev libtidy-dev libxslt1-dev autoconf \
    re2c libmysqlclient-dev libsqlite3-dev libbz2-dev \
    php5-cli sqlite3

#------------------------------------------------
# composer
#------------------------------------------------
RUN cd /tmp && \
    curl -sS https://getcomposer.org/installer | php && \
    mv composer.phar /usr/local/bin/composer && chmod 755 /usr/local/bin/composer

#------------------------------------------------
# Cache clean
#------------------------------------------------
RUN apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

#------------------------------------------------
# Japanese
#------------------------------------------------
RUN unlink /etc/localtime && \
    ln -s /urs/share/zoneinfo/Japan /etc/localtim && \
    locale-gen ja_JP.UTF-8

#------------------------------------------------
# Create local user
#------------------------------------------------
RUN export USERNAME=developer && \
    adduser --disabled-password --gecos "" ${USERNAME} && \
    echo "${USERNAME}:%{USERNAME}" | chpasswd && \
    mkdir -m 700 /home/${USERNAME}/.ssh && \
    chown ${USERNAME}:${USERNAME} -R /home/${USERNAME} && \
    export SUDOFILE='/etc/sudoers.d/developer' && \
    echo 'developer ALL=(ALL) NOPASSWD: ALL' >> ${SUDOFILE} && \
    chmod 0440 ${SUDOFILE}

USER developer
ENV HOME /home/developer
WORKDIR /home/developer

#------------------------------------------------
# phpenv
#------------------------------------------------
RUN curl https://raw.githubusercontent.com/CHH/phpenv/master/bin/phpenv-install.sh | bash && \
    echo 'export PATH="${HOME}/.composer/vendor/bin:${HOME}/.phpenv/bin:${HOME}/bin:$PATH"' >> ${HOME}/.bashrc && \
    echo 'eval "$(phpenv init -)"' >> ${HOME}/.bashrc && \
    mkdir ${HOME}/.phpenv/plugins && \
    git clone https://github.com/CHH/php-build.git ${HOME}/.phpenv/plugins/php-build
ENV PATH ${HOME}/.phpenv/shims:${HOME}/.phpenv/bin:$PATH

#------------------------------------------------
# php install
#------------------------------------------------
ADD ./installver /tmp/installver
RUN for ver in `cat /tmp/installver`; do \
      phpenv install $ver; \
      perl -pi -e 's#^;(date.timezone =).*#\1 Asia/Tokyo#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
    done && \
    phpenv global `head -n 1 /tmp/installver`

#------------------------------------------------
# phpcs, phpmd
#------------------------------------------------
RUN composer global require squizlabs/php_codesniffer=* && \
    composer global require phpmd/phpmd=* && \
    composer global require phpunit/phpunit=4.6.* && \
    composer global require robmorgan/phinx && \
    composer global require peridot-php/peridot:~1.15 && \
    composer global require codegyre/robo
ENV PATH ${HOME}/.composer/vendor/bin:${PATH}

CMD /usr/local/bin/netbeans
