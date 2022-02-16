# moodle-tool_cloudmetrics

## What is this?

This plugin is intended to be a generic admin tool for defining various realtime metrics of various sorts. 

Metrics may be 'built in' ones such as the same metrics which are beaconed back to Moodle HQ when you register you site, or they may be custom ones specific to your site.

In general a metric is any real time value that you might push to another service which monitors and tracks that metric over time such as a dataware house or a tool like AWS CloudWatch. Eventually this plugin may support publishing to many potential services.

## Metrics


## Services

Metrics may be sent to one or more different services.

### Build in admin report

TBA

We plan to have a very simple internal record of metrics with a limited data retention policy and basic graphing.


### AWS CloudWatch

TBA

https://aws.amazon.com/cloudwatch/

### Google Cloud Monitoring

TBA

https://cloud.google.com/monitoring

### Azure Monitor

TBA

https://docs.microsoft.com/en-us/azure/azure-monitor/overview
