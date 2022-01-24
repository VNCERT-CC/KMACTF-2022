FROM python:3-alpine

RUN apk add --update --no-cache tzdata
ENV TZ Asia/Ho_Chi_Minh
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apk add --update --no-cache coreutils

RUN apk add --no-cache --virtual .build-deps gcc libc-dev make
RUN apk add --no-cache python3 py3-pip python3-dev
RUN pip3 install --upgrade pip setuptools

# COPY ./requirements.txt /opt/requirements.txt
# RUN pip3 install -r /opt/requirements.txt
RUN pip3 install pyfiglet

RUN apk del .build-deps gcc libc-dev make

CMD [ "/opt/proxy-cmd", "-w", "/opt/app", "--", "python3", "-u", "figlet.py" ]
