# Use phusion/baseimage as base image. To make your builds reproducible, make
# sure you lock down to a specific version, not to `latest`!
# See https://github.com/phusion/baseimage-docker/blob/master/Changelog.md for
# a list of version numbers.
FROM phusion/baseimage:0.9.16

#------------------------------------------------
# Japanese
#------------------------------------------------
RUN unlink /etc/localtime && \
    ln -s /urs/share/zoneinfo/Japan /etc/localtime && \
    locale-gen ja_JP.UTF-8

ENV DEBIAN_FRONTEND noninteractive

# ...put your own build instructions here...
RUN sed -ie 's#archive#jp.archive#g' /etc/apt/sources.list && \
    sed -ie 's#main$#main universe#' /etc/apt/sources.list && \
    apt-get update && apt-get install -y software-properties-common && \
    add-apt-repository ppa:webupd8team/java -y && \
    apt-get update && \
    echo oracle-java8-installer shared/accepted-oracle-license-v1-1 select true | /usr/bin/debconf-set-selections && \
    apt-get install -y oracle-java8-installer libxext-dev libxrender-dev libxtst-dev && \
    apt-get install -y fonts-takao fonts-ipafont fontconfig && \
    mkdir /usr/lib/jvm/java-8-oracle/jre/lib/fonts/fallback && \
    ln -s /usr/share/fonts/truetype/takao-gothic/Takao* /usr/lib/jvm/java-8-oracle/jre/lib/fonts/fallback/

ADD state.xml /tmp/state.xml

RUN curl -L http://download.netbeans.org/netbeans/8.0.2/final/bundles/netbeans-8.0.2-php-linux.sh -o /tmp/netbeans.sh && \
    chmod +x /tmp/netbeans.sh && \
    echo 'Installing netbeans' && \
    /tmp/netbeans.sh --verbose --silent --state /tmp/state.xml && \
    sed -ie 's/^\(netbeans_default_options=\)"\(.*\)"/\1"\2 --fontsize 14 -J-Dawt.useSystemAAFontSettings=lcd"/' /usr/local/netbeans-8.0.2/etc/netbeans.conf && \
    rm -rf /tmp/*

#------------------------------------------------
# Install phpenv libraries
#------------------------------------------------
RUN apt-get install -y sudo ack-grep curl lftp jq ca-certificates \
    git-core make bison gcc cpp g++ subversion exuberant-ctags git-flow \
    libxml2-dev libssl-dev \
    libcurl4-gnutls-dev libjpeg-dev libpng12-dev libmcrypt-dev \
    libreadline-dev libtidy-dev libxslt1-dev autoconf \
    re2c libmysqlclient-dev libsqlite3-dev libbz2-dev \
    php5-cli sqlite3 mysql-common

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
ENV DEBIAN_FRONTEND dialog

#------------------------------------------------
# Create local user
#------------------------------------------------
ADD run /usr/local/bin/netbeans
RUN export USERNAME=developer && \
    adduser --disabled-password --gecos "" ${USERNAME} && \
    echo "${USERNAME}:%{USERNAME}" | chpasswd && \
    mkdir -m 700 /home/${USERNAME}/.ssh && \
    chown ${USERNAME}:${USERNAME} -R /home/${USERNAME} && \
    export SUDOFILE='/etc/sudoers.d/developer' && \
    echo 'developer ALL=(ALL) NOPASSWD: ALL' >> ${SUDOFILE} && \
    chmod 0440 ${SUDOFILE} && \
    update-alternatives --set editor /usr/bin/vim.basic

USER developer
ENV HOME /home/developer
ENV LC_ALL ja_JP.UTF-8
ENV LANG ja_JP.UTF-8
WORKDIR /home/developer

ADD fonts ${HOME}/.fonts
RUN fc-cache -fv

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
      perl -pi -e 's#^(expose =).*#\1 Off#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^(display_errors =).*#\1 On#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^(allow_url_fopen =).*#\1 On#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^(allow_url_include =).*#\1 Off#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^(file_upload =).*#\1 Off#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^(sql.safe_mode =).*#\1 On#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^;(magic_quotes_gpc =).*#\1 Off#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^(session.use_strict_mode =).*#\1 1#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^;(session.cookie_secure =).*#\1 1#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^(session.cookie_httponly =).*#\1 1#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^;(mbstring.language =).*#\1 Japanese#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^;(mbstring.internal_encoding =).*#\1 UTF-8#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^;(mbstring.encoding_translation =).*#\1 Off#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
      perl -pi -e 's#^;(mbstring.http_output =).*#\1 pass#g' ${HOME}/.phpenv/versions/${ver}/etc/php.ini;  \
    done && \
    phpenv global `head -n 1 /tmp/installver`

#------------------------------------------------
# phpcs, phpmd
#------------------------------------------------
RUN composer global require squizlabs/php_codesniffer && \
    composer global require phpmd/phpmd && \
    composer global require fabpot/php-cs-fixer && \
    composer global require apigen/apigen --dev && \
    composer global require phpunit/phpunit=4.6.* && \
    composer global require phpunit/phpunit-skeleton-generator=*
ENV PATH ${HOME}/.composer/vendor/bin:${PATH}

CMD /usr/local/bin/netbeans
