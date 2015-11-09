#!/usr/bin/env ruby

require 'rss'
require 'open-uri'
require 'awesome_print'

require 'upwork/api'
require 'upwork/api/routers/auth'
require 'upwork/api/routers/jobs/profile'

require 'telegram/bot'

require 'pg'
require 'sequel'

config = Upwork::Api::Config.new({
  'consumer_key'    => '0b890e685dbaa9fddaf51df47f924096',
  'consumer_secret' => '70590fda6583b2db',
  'access_token'    => '6cd6202680a20796889a537ae28bb51e', # assign if known
  'access_secret'   => '2d2c4ec57fd374c4', # assign if known
  'debug'           => false
})

client = Upwork::Api::Client.new(config)
profile = Upwork::Api::Routers::Jobs::Profile.new(client)

#url = 'https://www.upwork.com/find-work-home/rss?securityToken=4e1d5cae275886dd0b262cf11aef00f4f8076885f911a82d6b2636bd88872879%7Ed77f90f'
url = 'https://www.upwork.com/jobs/rss?cn1[]=Web%2C+Mobile+%26+Software+Dev&cn2[]=Web+Development&t[]=0&t[]=1&dur[]=0&dur[]=1&dur[]=13&dur[]=26&dur[]=none&wl[]=10&wl[]=30&wl[]=none&tba[]=0&tba[]=1-9&tba[]=10-&exp[]=1&exp[]=2&exp[]=3&amount[]=Min&amount[]=Max&sortBy=s_ctime+desc'

open(url) do |rss|
  notifications = RSS::Parser.parse(rss).items.map do |item|
    job_id = "~#{item.link[/\%7E(\w*)?/, 1]}"
    title = item.title.gsub ' - Upwork', ''
    puts "#{job_id} : #{title}"

    if db[:upwork_jobs][id: job_id].nil?
      info = profile.get_specific(job_id)['profile']

      if info['op_required_skills'] && info['op_required_skills']['op_required_skill']
        skills =  info['op_required_skills']['op_required_skill']
        if skills.is_a?(Hash)
          skills = skills.values
        elsif skills.is_a?(Array)
          skills.map!(&:values).flatten!.uniq! unless skills.nil?
        end
      else
        skills = skills.values unless skills.nil?
      end

      row = {
        id: job_id,
        url: item.link,
        created_at: Time.at(info['op_ctime'].to_i/1000),
        title: info['op_title'],
        description: info['op_description'],
        type: info['job_type'],
        budget: (info['amount'].to_i || 0),
        engagement: info['op_engagement'],
        engagement_weeks: info['engagement_weeks'],
        contractor_tier: info['op_contractor_tier'].to_i,
        skills: skills.nil? ? '' : skills.join(',')
      }

      ap row # !!!!

      db[:upwork_jobs].insert row
    end

    row
  end
end
