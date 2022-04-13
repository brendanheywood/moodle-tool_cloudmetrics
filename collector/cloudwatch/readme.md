# cltr_cloudwatch

Plugin to provide a collector for [AWS Cloudwatch](https://docs.aws.amazon.com/cloudwatch/)
to be used with tool_cloudmetrics.

## Requires
- [local/aws](https://github.com/catalyst/moodle-local_aws) plugin.
- AWS account
- IAM user.

## IAM Access

You need to have an IAM user connected to your AWS account.
This user will need permissions to submit cloudwatch data.

This is the minimum user policy for access.

    {
        "Version": "2012-10-17",
        "Statement": [{
            "Effect": "Allow",
            "Action": [
                "cloudwatch:PutMetricData"
            ],
            "Resource": "*"
        }]
    }

Or the user can be assigned the `CloudWatchFullAccess`
predefined policy.

Ideally, the execution environent will be configured with
the IAM credentials. However, the following config can
also be used.

    $CFG->cltr_cloudwatch = [
        'credentials' => [
            'key' => '<Access key ID>',
            'secret' => '<Secret access key>',
        ],
    ];


Also see [Cloudwatch IAM policy docs](https://docs.aws.amazon.com/AmazonCloudWatch/latest/monitoring/iam-identity-based-access-control-cw.html).

## Settings



