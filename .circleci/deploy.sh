#!/bin/sh
set -ex

export AWS_ACCESS_KEY_ID=${AWS_ACCESS_KEY_ID}
export AWS_SECRET_ACCESS_KEY=${AWS_SECRET_ACCESS_KEY}
export AWS_DEFAULT_REGION="ap-northeast-1"

export DEPLOY_USER=${USER_NAME}
export HOST=${HOST_NAME}

MY_SECURITY_GROUP=${DEPLOY_SG_ID}
MY_IP=`curl -f -s ifconfig.me`

trap "aws ec2 revoke-security-group-ingress --group-id $MY_SECURITY_GROUP --protocol tcp --port 22 --cidr $MY_IP/32" 0 1 2 3 15
aws ec2 authorize-security-group-ingress --group-id $MY_SECURITY_GROUP --protocol tcp --port 22 --cidr $MY_IP/32
ssh $DEPLOY_USER@$HOST 'sh' < .circleci/remote.sh hello